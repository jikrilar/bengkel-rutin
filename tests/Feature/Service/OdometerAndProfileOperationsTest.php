<?php

use App\Actions\Service\AssignServiceProfileAction;
use App\Actions\Service\UpdateServiceProfileAction;
use App\Actions\Vehicle\CorrectOdometerAction;
use App\Enums\BaselineSource;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Models\FuzzyCalculation;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 12:00:00');
    $this->seed([FuzzyConfigSeeder::class, FuzzyRuleSeeder::class]);
    $this->admin = User::factory()->admin()->create();
    $this->customer = User::factory()->create();
    $this->profile = ServiceProfile::factory()->create(['interval_km' => 4000, 'interval_days' => 120]);
    $this->vehicle = Vehicle::factory()->for($this->customer)->for($this->profile)->create([
        'baseline_service_date' => '2026-06-01',
        'baseline_odometer' => 10000,
        'baseline_source' => BaselineSource::CustomerInput,
    ]);
    OdometerLog::factory()->for($this->vehicle)->create([
        'odometer' => 12000,
        'recorded_at' => '2026-09-21 10:00:00',
        'source' => OdometerSource::CustomerUpdate,
    ]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('creates a traceable admin odometer correction and recalculates recommendation', function () {
    $log = app(CorrectOdometerAction::class)->execute(
        $this->admin,
        $this->vehicle,
        12150,
        'Koreksi setelah verifikasi panel instrumen.',
    );

    expect($log->source)->toBe(OdometerSource::AdminCorrection)
        ->and($log->recorded_by)->toBe($this->admin->id)
        ->and($log->correction_reason)->toContain('verifikasi')
        ->and($this->vehicle->fresh()->latestOdometer->id)->toBe($log->id)
        ->and($this->vehicle->fresh()->latestFuzzyCalculation->trigger_type)->toBe(CalculationTrigger::ManualRecalculate);
});

it('requires a correction reason and non-decreasing corrected value', function () {
    expect(fn () => app(CorrectOdometerAction::class)->execute($this->admin, $this->vehicle, 12100, ''))
        ->toThrow(ValidationException::class)
        ->and(fn () => app(CorrectOdometerAction::class)->execute($this->admin, $this->vehicle, 11999, 'Koreksi valid.'))
        ->toThrow(ValidationException::class);

    expect(OdometerLog::query()->count())->toBe(1);
});

it('assigns an active service profile and preserves prior fuzzy history', function () {
    app(RecommendationService::class)->calculateAndPersist(
        $this->vehicle,
        CalculationTrigger::ManualRecalculate,
    );
    $oldCalculationId = $this->vehicle->fresh()->latestFuzzyCalculation->id;
    $newProfile = ServiceProfile::factory()->create(['interval_km' => 6000, 'interval_days' => 180]);

    app(AssignServiceProfileAction::class)->execute($this->admin, $this->vehicle, $newProfile);

    $vehicle = $this->vehicle->fresh();
    expect($vehicle->service_profile_id)->toBe($newProfile->id)
        ->and($vehicle->latestFuzzyCalculation->trigger_type)->toBe(CalculationTrigger::ServiceProfileChanged)
        ->and($vehicle->latestFuzzyCalculation->interval_km_snapshot)->toBe(6000)
        ->and(FuzzyCalculation::query()->whereKey($oldCalculationId)->exists())->toBeTrue()
        ->and($vehicle->fuzzyCalculations()->count())->toBe(2);
});

it('rejects invalid service profile intervals and recalculates assigned vehicles on interval change', function () {
    expect(fn () => app(UpdateServiceProfileAction::class)->execute($this->admin, $this->profile, [
        'name' => 'Invalid',
        'interval_km' => 0,
        'interval_days' => -1,
        'is_active' => true,
    ]))->toThrow(ValidationException::class);

    app(UpdateServiceProfileAction::class)->execute($this->admin, $this->profile, [
        'name' => 'Interval Panjang',
        'interval_km' => 5000,
        'interval_days' => 150,
        'description' => null,
        'is_active' => true,
    ]);

    expect($this->profile->fresh()->interval_km)->toBe(5000)
        ->and($this->vehicle->fresh()->latestFuzzyCalculation->trigger_type)->toBe(CalculationTrigger::ServiceProfileChanged)
        ->and($this->vehicle->fresh()->latestFuzzyCalculation->interval_days_snapshot)->toBe(150);
});

it('forbids customer access to admin odometer and profile operations', function () {
    $profile = ServiceProfile::factory()->create();

    expect(fn () => app(CorrectOdometerAction::class)->execute($this->customer, $this->vehicle, 12100, 'Tidak berhak.'))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => app(AssignServiceProfileAction::class)->execute($this->customer, $this->vehicle, $profile))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => app(UpdateServiceProfileAction::class)->execute($this->customer, $profile, [
            'name' => 'Unauthorized',
            'interval_km' => 4000,
            'interval_days' => 120,
        ]))->toThrow(AuthorizationException::class);
});
