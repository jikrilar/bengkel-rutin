<?php

use App\Enums\BaselineSource;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyRuleResult;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Database\Seeders\ServiceProfileSeeder;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 10:00:00');
    $this->withSession(['_token' => 'test-token']);
    $this->seed([
        ServiceProfileSeeder::class,
        FuzzyConfigSeeder::class,
        FuzzyRuleSeeder::class,
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/** @return array<string, mixed> */
function t05VehiclePayload(array $overrides = []): array
{
    return array_merge([
        '_token' => 'test-token',
        'name' => 'Mobil Keluarga',
        'brand' => 'Toyota',
        'model' => 'Avanza',
        'year' => 2022,
        'plate_number' => 'B 1234 XYZ',
        'current_odometer' => 12000,
        'knows_last_service' => '1',
        'last_service_date' => '2026-06-22',
        'last_service_odometer' => 9000,
    ], $overrides);
}

it('creates a vehicle, initial odometer, and recommendation atomically when baseline is complete', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('vehicles.store'), t05VehiclePayload());

    $vehicle = Vehicle::query()->sole();
    $response->assertRedirect(route('vehicles.show', $vehicle));

    expect($vehicle->user_id)->toBe($customer->id)
        ->and($vehicle->plate_number_normalized)->toBe('B1234XYZ')
        ->and($vehicle->baseline_source)->toBe(BaselineSource::CustomerInput)
        ->and($vehicle->odometerLogs)->toHaveCount(1)
        ->and($vehicle->latestOdometer->odometer)->toBe(12000)
        ->and($vehicle->latestOdometer->source)->toBe(OdometerSource::Initial)
        ->and($vehicle->latestFuzzyCalculation)->not->toBeNull()
        ->and($vehicle->latestFuzzyCalculation->trigger_type)->toBe(CalculationTrigger::VehicleCreated)
        ->and($vehicle->latestFuzzyCalculation->ruleResults)->not->toBeEmpty();
});

it('creates an initial odometer but keeps recommendation unavailable when baseline is unknown', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post(route('vehicles.store'), t05VehiclePayload([
        'plate_number' => 'D 8021 QA',
        'knows_last_service' => '0',
        'last_service_date' => null,
        'last_service_odometer' => null,
    ]))->assertRedirect();

    $vehicle = Vehicle::query()->sole();

    expect($vehicle->baseline_service_date)->toBeNull()
        ->and($vehicle->baseline_odometer)->toBeNull()
        ->and($vehicle->odometerLogs)->toHaveCount(1)
        ->and($vehicle->fuzzyCalculations)->toHaveCount(0);

    $this->actingAs($customer)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertSee('Rekomendasi Belum Tersedia');
});

it('enforces normalized plate uniqueness across display formats', function () {
    $customer = User::factory()->create();
    Vehicle::factory()->for($customer)->create(['plate_number' => 'B 1234 XYZ']);

    $this->actingAs($customer)
        ->from(route('vehicles.create'))
        ->post(route('vehicles.store'), t05VehiclePayload(['plate_number' => 'b-1234-xyz']))
        ->assertRedirect(route('vehicles.create'))
        ->assertSessionHasErrors('plate_number');

    expect(Vehicle::query()->count())->toBe(1)
        ->and(OdometerLog::query()->count())->toBe(0)
        ->and(FuzzyCalculation::query()->count())->toBe(0);
});

it('only updates safe vehicle identity fields', function () {
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create([
        'baseline_service_date' => '2026-06-22',
        'baseline_odometer' => 9000,
    ]);

    $this->actingAs($customer)->put(route('vehicles.update', $vehicle), [
        '_token' => 'test-token',
        'name' => 'Mobil Utama',
        'brand' => 'Toyota',
        'model' => 'Veloz',
        'year' => 2023,
        'plate_number' => 'B 1234 QRS',
        'baseline_service_date' => '2026-01-01',
        'baseline_odometer' => 1,
        'service_profile_id' => 999,
    ])->assertRedirect(route('vehicles.show', $vehicle));

    $vehicle->refresh();

    expect($vehicle->name)->toBe('Mobil Utama')
        ->and($vehicle->model)->toBe('Veloz')
        ->and($vehicle->baseline_service_date->toDateString())->toBe('2026-06-22')
        ->and($vehicle->baseline_odometer)->toBe(9000)
        ->and($vehicle->service_profile_id)->not->toBe(999);
});

it('adds an odometer log and recalculates the persisted recommendation', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->post(route('vehicles.store'), t05VehiclePayload());
    $vehicle = Vehicle::query()->sole();
    $firstCalculationId = $vehicle->latestFuzzyCalculation->id;

    $this->travelTo('2026-09-23 09:00:00');
    $response = $this->actingAs($customer)->post(route('vehicles.odometer.store', $vehicle), [
        '_token' => 'test-token',
        'odometer' => 12500,
        'recorded_at' => '2026-09-23 09:00:00',
    ]);

    $response->assertRedirect(route('vehicles.show', $vehicle))
        ->assertSessionHas('success');

    $vehicle->refresh();

    expect($vehicle->odometerLogs()->count())->toBe(2)
        ->and($vehicle->latestOdometer->odometer)->toBe(12500)
        ->and($vehicle->latestOdometer->source)->toBe(OdometerSource::CustomerUpdate)
        ->and($vehicle->fuzzyCalculations()->count())->toBe(2)
        ->and($vehicle->latestFuzzyCalculation->id)->not->toBe($firstCalculationId)
        ->and($vehicle->latestFuzzyCalculation->trigger_type)->toBe(CalculationTrigger::OdometerUpdated)
        ->and($vehicle->latestFuzzyCalculation->ruleResults)->not->toBeEmpty();
});

it('rejects an odometer lower than the latest valid reading without partial writes', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->post(route('vehicles.store'), t05VehiclePayload());
    $vehicle = Vehicle::query()->sole();

    $this->actingAs($customer)
        ->from(route('vehicles.show', $vehicle))
        ->post(route('vehicles.odometer.store', $vehicle), [
            '_token' => 'test-token',
            'odometer' => 11999,
            'recorded_at' => '2026-09-22 10:00:00',
        ])
        ->assertRedirect(route('vehicles.show', $vehicle))
        ->assertSessionHasErrors('odometer');

    expect($vehicle->odometerLogs()->count())->toBe(1)
        ->and($vehicle->fuzzyCalculations()->count())->toBe(1);
});

it('blocks customers from viewing or editing another customers vehicle and calculation', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $vehicle = Vehicle::factory()->for($owner)->create();
    $calculation = FuzzyCalculation::factory()->for($vehicle)->create();

    $this->actingAs($other)->get(route('vehicles.show', $vehicle))->assertForbidden();
    $this->actingAs($other)->get(route('vehicles.edit', $vehicle))->assertForbidden();
    $this->actingAs($other)->put(route('vehicles.update', $vehicle), [
        '_token' => 'test-token',
        'name' => 'Bukan Milik Saya',
        'brand' => 'Honda',
        'model' => 'City',
        'year' => 2020,
        'plate_number' => 'F 1111 BAD',
    ])->assertForbidden();
    $this->actingAs($other)
        ->get(route('recommendations.calculation.show', [$vehicle, $calculation]))
        ->assertForbidden();
});

it('renders recommendation and fuzzy technical detail from the persisted snapshot', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->post(route('vehicles.store'), t05VehiclePayload());
    $vehicle = Vehicle::query()->sole();
    $calculation = $vehicle->latestFuzzyCalculation;
    $calculationCount = FuzzyCalculation::query()->count();
    $ruleResultCount = FuzzyRuleResult::query()->count();

    $this->actingAs($customer)
        ->get(route('recommendations.show', $vehicle))
        ->assertOk()
        ->assertSee('Dasar rekomendasi')
        ->assertSee($calculation->final_status->label());

    $this->actingAs($customer)
        ->get(route('recommendations.calculation.show', [$vehicle, $calculation]))
        ->assertOk()
        ->assertSee('Detail Perhitungan Fuzzy')
        ->assertSee('Derajat keanggotaan')
        ->assertSee('Rule aktif')
        ->assertSee('Defuzzifikasi')
        ->assertSee($calculation->ruleResults->first()->rule->code);

    expect(FuzzyCalculation::query()->count())->toBe($calculationCount)
        ->and(FuzzyRuleResult::query()->count())->toBe($ruleResultCount);
});

it('renders dashboard states and the customer vehicle selector', function () {
    $customer = User::factory()->create(['name' => 'Dewi Pratama']);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Belum ada kendaraan');

    Vehicle::factory()->count(2)->for($customer)->create([
        'baseline_service_date' => null,
        'baseline_odometer' => null,
        'baseline_source' => null,
    ])->each(fn (Vehicle $vehicle) => OdometerLog::factory()->for($vehicle)->create());

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Kendaraan aktif')
        ->assertSee('Baseline servis belum lengkap');
});
