<?php

use App\Enums\BaselineSource;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Exceptions\Fuzzy\InvalidFuzzyRuleSetException;
use App\Exceptions\Recommendation\RecommendationUnavailableException;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyRule;
use App\Models\FuzzyRuleResult;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;

beforeEach(function () {
    $this->seed([
        FuzzyConfigSeeder::class,
        FuzzyRuleSeeder::class,
    ]);
});

function recommendationVehicle(
    string $baselineDate = '2026-01-01',
    int $baselineOdometer = 10000,
): Vehicle {
    $profile = ServiceProfile::factory()->create([
        'interval_km' => 4000,
        'interval_days' => 120,
    ]);

    return Vehicle::factory()->create([
        'service_profile_id' => $profile->id,
        'baseline_service_date' => $baselineDate,
        'baseline_odometer' => $baselineOdometer,
        'baseline_source' => BaselineSource::CustomerInput,
    ]);
}

it('persists a complete reproducible recommendation snapshot and active rules', function () {
    $vehicle = recommendationVehicle();
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 11000,
        'recorded_at' => '2026-03-02 08:00:00',
        'source' => OdometerSource::CustomerUpdate,
    ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 12000,
        'recorded_at' => '2026-04-01 08:00:00',
        'source' => OdometerSource::CustomerUpdate,
    ]);

    $result = app(RecommendationService::class)->calculateAndPersist(
        $vehicle,
        CalculationTrigger::ManualRecalculate,
        new DateTimeImmutable('2026-04-01 12:00:00'),
    );
    $calculation = FuzzyCalculation::query()->sole();

    expect($result->usageFallbackApplied)->toBeFalse()
        ->and($result->fuzzyResult->ruleResults)->toHaveCount(18)
        ->and($calculation->vehicle->is($vehicle))->toBeTrue()
        ->and($calculation->fuzzyConfig->version)->toBe(1)
        ->and($calculation->trigger_type)->toBe(CalculationTrigger::ManualRecalculate)
        ->and($calculation->interval_km_snapshot)->toBe(4000)
        ->and($calculation->interval_days_snapshot)->toBe(120)
        ->and($calculation->current_odometer)->toBe(12000)
        ->and($calculation->baseline_odometer)->toBe(10000)
        ->and($calculation->km_since_service)->toBe(2000)
        ->and($calculation->days_since_service)->toBe(90)
        ->and((float) $calculation->average_daily_km)->toEqualWithDelta(33.33, 0.01)
        ->and((float) $calculation->baseline_daily_usage)->toEqualWithDelta(33.33, 0.01)
        ->and((float) $calculation->progress_km)->toBe(50.0)
        ->and((float) $calculation->progress_time)->toBe(75.0)
        ->and((float) $calculation->usage_intensity)->toBe(100.0)
        ->and($calculation->score)->not->toBeNull()
        ->and($calculation->fuzzy_status)->toBe(RecommendationStatus::Approaching)
        ->and($calculation->estimated_due_by_km)->not->toBeNull()
        ->and($calculation->estimated_due_by_time)->not->toBeNull()
        ->and($calculation->estimated_due_date)->not->toBeNull()
        ->and($calculation->recommended_date)->not->toBeNull()
        ->and($calculation->final_status)->toBe(RecommendationStatus::Approaching)
        ->and($calculation->guard_applied)->toBeFalse()
        ->and($vehicle->fresh()->latestFuzzyCalculation->is($calculation))->toBeTrue();

    expect($calculation->ruleResults)->not->toBeEmpty()
        ->and($calculation->ruleResults)->toHaveCount(count($result->fuzzyResult->activeRuleResults()))
        ->and($calculation->ruleResults->every(
            fn (FuzzyRuleResult $rule): bool => (float) $rule->alpha > 0
                && (float) $rule->weighted_value === round((float) $rule->alpha * (float) $rule->z_value, 4),
        ))->toBeTrue()
        ->and((float) $calculation->score)->toEqualWithDelta(
            $calculation->ruleResults->sum('weighted_value') / $calculation->ruleResults->sum('alpha'),
            0.01,
        );
});

it('persists the deterministic neutral usage fallback', function () {
    $vehicle = recommendationVehicle();
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 12000,
        'recorded_at' => '2026-04-01 08:00:00',
    ]);

    $result = app(RecommendationService::class)->calculateAndPersist(
        $vehicle,
        CalculationTrigger::VehicleCreated,
        new DateTimeImmutable('2026-04-01'),
    );
    $calculation = FuzzyCalculation::query()->sole();

    expect($result->usageFallbackApplied)->toBeTrue()
        ->and($result->averageDailyKm)->toEqualWithDelta($result->baselineDailyUsage, 0.000001)
        ->and($result->usageIntensity)->toBe(100.0)
        ->and((float) $calculation->average_daily_km)->toEqualWithDelta(33.33, 0.01)
        ->and((float) $calculation->usage_intensity)->toBe(100.0);
});

it('escalates final status for an imminent projection without changing fuzzy output', function () {
    $vehicle = recommendationVehicle();
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 10000,
        'recorded_at' => '2026-03-23 08:00:00',
    ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 11200,
        'recorded_at' => '2026-03-25 08:00:00',
    ]);

    $result = app(RecommendationService::class)->calculateAndPersist(
        $vehicle,
        CalculationTrigger::OdometerUpdated,
        new DateTimeImmutable('2026-03-25'),
    );
    $calculation = FuzzyCalculation::query()->sole();

    expect($result->fuzzyResult->score)->toBe(0.0)
        ->and($result->fuzzyStatus)->toBe(RecommendationStatus::NotNeeded)
        ->and($result->finalStatus)->toBe(RecommendationStatus::Urgent)
        ->and($result->guardApplied)->toBeTrue()
        ->and($result->guardReason)->not->toBeNull()
        ->and((float) $calculation->score)->toBe(0.0)
        ->and($calculation->fuzzy_status)->toBe(RecommendationStatus::NotNeeded)
        ->and($calculation->final_status)->toBe(RecommendationStatus::Urgent)
        ->and($calculation->guard_applied)->toBeTrue();
});

it('does not persist a recommendation when required baseline data is incomplete', function () {
    $vehicle = Vehicle::factory()->create([
        'baseline_service_date' => null,
        'baseline_odometer' => null,
        'baseline_source' => null,
    ]);
    OdometerLog::factory()->for($vehicle)->create();

    expect(fn () => app(RecommendationService::class)->calculateAndPersist(
        $vehicle,
        CalculationTrigger::ManualRecalculate,
        new DateTimeImmutable('2026-04-01'),
    ))->toThrow(RecommendationUnavailableException::class);

    expect(FuzzyCalculation::query()->count())->toBe(0);
});

it('rejects a noncanonical rule set before writing calculation history', function () {
    $vehicle = recommendationVehicle();
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 12000,
        'recorded_at' => '2026-04-01 08:00:00',
    ]);
    FuzzyRule::query()->where('code', 'R18')->delete();

    expect(fn () => app(RecommendationService::class)->calculateAndPersist(
        $vehicle,
        CalculationTrigger::ManualRecalculate,
        new DateTimeImmutable('2026-04-01'),
    ))->toThrow(InvalidFuzzyRuleSetException::class);

    expect(FuzzyCalculation::query()->count())->toBe(0)
        ->and(FuzzyRuleResult::query()->count())->toBe(0);
});
