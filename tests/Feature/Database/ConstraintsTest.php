<?php

use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyRule;
use App\Models\FuzzyRuleResult;
use App\Models\OdometerLog;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\FuzzyRuleSeeder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;

it('normalizes plate numbers and rejects equivalent duplicates', function () {
    $vehicle = Vehicle::factory()->create(['plate_number' => 'f 1234 abc']);

    expect($vehicle->plate_number)->toBe('F 1234 ABC')
        ->and($vehicle->plate_number_normalized)->toBe('F1234ABC');

    expect(fn () => Vehicle::factory()->create(['plate_number' => 'F-1234-ABC']))
        ->toThrow(QueryException::class);
});

it('enforces foreign keys for historical odometer data', function () {
    expect(fn () => OdometerLog::factory()->create(['vehicle_id' => 999999]))
        ->toThrow(QueryException::class);
});

it('enforces one service record per booking', function () {
    $vehicle = Vehicle::factory()->create();
    $booking = Booking::factory()->for($vehicle)->create();
    $admin = User::factory()->admin()->create();

    ServiceRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'booking_id' => $booking->id,
        'completed_by' => $admin->id,
    ]);

    expect(fn () => ServiceRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'booking_id' => $booking->id,
        'completed_by' => $admin->id,
    ]))->toThrow(QueryException::class);
});

it('enforces one result per rule in each fuzzy calculation', function () {
    $calculation = FuzzyCalculation::factory()->create();
    $this->seed(FuzzyRuleSeeder::class);
    $rule = FuzzyRule::query()->firstOrFail();

    FuzzyRuleResult::query()->create([
        'fuzzy_calculation_id' => $calculation->id,
        'fuzzy_rule_id' => $rule->id,
        'alpha' => 0.5,
        'z_value' => 60,
        'weighted_value' => 30,
    ]);

    expect(fn () => FuzzyRuleResult::query()->create([
        'fuzzy_calculation_id' => $calculation->id,
        'fuzzy_rule_id' => $rule->id,
        'alpha' => 0.4,
        'z_value' => 50,
        'weighted_value' => 20,
    ]))->toThrow(QueryException::class);
});

it('does not expose destructive soft-delete workflows on historical models', function () {
    foreach ([
        OdometerLog::class,
        FuzzyCalculation::class,
        FuzzyRuleResult::class,
        Booking::class,
        ServiceRecord::class,
    ] as $model) {
        expect(class_uses_recursive($model))->not->toContain(SoftDeletes::class);
    }
});
