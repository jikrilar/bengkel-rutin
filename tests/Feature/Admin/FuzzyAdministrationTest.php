<?php

use App\Actions\Fuzzy\ActivateFuzzyConfigAction;
use App\Enums\BaselineSource;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Filament\Pages\FuzzyRules;
use App\Filament\Resources\FuzzyConfigs\FuzzyConfigResource;
use App\Jobs\RecalculateVehiclesForFuzzyConfig;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyConfig;
use App\Models\FuzzyRule;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed([FuzzyConfigSeeder::class, FuzzyRuleSeeder::class]);
});

it('creates a new active fuzzy version without overwriting history', function () {
    Queue::fake();
    $admin = User::factory()->admin()->create();
    $old = FuzzyConfig::query()->active()->sole();
    $historical = FuzzyCalculation::factory()->for($old, 'fuzzyConfig')->create();

    $new = app(ActivateFuzzyConfigAction::class)->execute($admin, [
        'progress_safe_end' => 68,
        'progress_approaching_peak' => 88,
        'progress_critical_full' => 100,
        'usage_normal_full_until' => 78,
        'usage_intensive_full_from' => 125,
    ]);

    expect($new->version)->toBe($old->version + 1)
        ->and($new->is_active)->toBeTrue()
        ->and($old->fresh()->is_active)->toBeFalse()
        ->and(FuzzyConfig::query()->count())->toBe(2)
        ->and(FuzzyConfig::query()->active()->count())->toBe(1)
        ->and($historical->fresh()->fuzzy_config_id)->toBe($old->id);
    Queue::assertPushed(RecalculateVehiclesForFuzzyConfig::class, fn ($job) => $job->fuzzyConfigId === $new->id);
});

it('rejects invalid threshold ordering and non-admin changes', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    $invalid = [
        'progress_safe_end' => 90,
        'progress_approaching_peak' => 70,
        'progress_critical_full' => 100,
        'usage_normal_full_until' => 120,
        'usage_intensive_full_from' => 80,
    ];

    expect(fn () => app(ActivateFuzzyConfigAction::class)->execute($admin, $invalid))
        ->toThrow(ValidationException::class)
        ->and(fn () => app(ActivateFuzzyConfigAction::class)->reset($customer))
        ->toThrow(AuthorizationException::class)
        ->and(FuzzyConfig::query()->count())->toBe(1)
        ->and(FuzzyConfig::query()->active()->count())->toBe(1);
});

it('resets through a new default version and recalculates eligible vehicles without changing old snapshots', function () {
    Queue::fake();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    $profile = ServiceProfile::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->for($profile)->create([
        'baseline_service_date' => '2026-08-01',
        'baseline_odometer' => 10000,
        'baseline_source' => BaselineSource::CustomerInput,
    ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 11000,
        'recorded_at' => now()->subDay(),
        'source' => OdometerSource::CustomerUpdate,
        'recorded_by' => $customer->id,
    ]);
    app(RecommendationService::class)->calculateAndPersist($vehicle, CalculationTrigger::ManualRecalculate);
    $historical = $vehicle->fresh()->latestFuzzyCalculation;

    $custom = app(ActivateFuzzyConfigAction::class)->execute($admin, [
        'progress_safe_end' => 65,
        'progress_approaching_peak' => 85,
        'progress_critical_full' => 100,
        'usage_normal_full_until' => 75,
        'usage_intensive_full_from' => 125,
    ]);
    $default = app(ActivateFuzzyConfigAction::class)->reset($admin);
    (new RecalculateVehiclesForFuzzyConfig($default->id))->handle(app(RecommendationService::class));

    $latest = $vehicle->fresh()->latestFuzzyCalculation;
    expect($custom->fresh()->is_active)->toBeFalse()
        ->and($default->version)->toBe(3)
        ->and($default->only(array_keys(ActivateFuzzyConfigAction::DEFAULTS)))
        ->toMatchArray(array_map(fn ($value) => number_format($value, 2, '.', ''), ActivateFuzzyConfigAction::DEFAULTS))
        ->and(FuzzyConfig::query()->active()->count())->toBe(1)
        ->and($latest->trigger_type)->toBe(CalculationTrigger::FuzzyConfigChanged)
        ->and($latest->fuzzy_config_id)->toBe($default->id)
        ->and($historical->fresh()->fuzzy_config_id)->not->toBe($default->id)
        ->and(FuzzyCalculation::query()->whereKey($historical->id)->exists())->toBeTrue();
});

it('keeps exactly 18 rules and exposes them through a read-only admin page', function () {
    $admin = User::factory()->admin()->create();

    expect(FuzzyRule::query()->count())->toBe(18)
        ->and(FuzzyConfigResource::canCreate())->toBeFalse();

    $this->actingAs($admin)->get(FuzzyRules::getUrl())
        ->assertOk()
        ->assertSee('R01')
        ->assertSee('R18')
        ->assertSee('tidak dapat menambah, mengubah, atau menghapus rule');
    $this->actingAs($admin)->get(FuzzyConfigResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Buat versi baru')
        ->assertSee('Reset ke default');
});

it('prevents customers from opening fuzzy administration', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get(FuzzyRules::getUrl())->assertForbidden();
    $this->actingAs($customer)->get(FuzzyConfigResource::getUrl('index'))->assertForbidden();
});
