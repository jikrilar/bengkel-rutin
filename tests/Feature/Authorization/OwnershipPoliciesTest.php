<?php

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Gate;

it('limits customer vehicle access to the owner', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $vehicle = Vehicle::factory()->for($owner)->create();

    expect(Gate::forUser($owner)->allows('view', $vehicle))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $vehicle))->toBeTrue()
        ->and(Gate::forUser($otherCustomer)->allows('view', $vehicle))->toBeFalse()
        ->and(Gate::forUser($otherCustomer)->allows('update', $vehicle))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $vehicle))->toBeTrue();
});

it('enforces ownership through the booking vehicle chain', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $vehicle = Vehicle::factory()->for($owner)->create();
    $booking = Booking::factory()->for($vehicle)->create();

    expect(Gate::forUser($owner)->allows('view', $booking))->toBeTrue()
        ->and(Gate::forUser($otherCustomer)->allows('view', $booking))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $booking))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $booking))->toBeFalse();
});

it('enforces ownership for service history and fuzzy calculations', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $vehicle = Vehicle::factory()->for($owner)->create();
    $record = ServiceRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'completed_by' => $admin->id,
    ]);
    $calculation = FuzzyCalculation::factory()->for($vehicle)->create();

    expect(Gate::forUser($owner)->allows('view', $record))->toBeTrue()
        ->and(Gate::forUser($otherCustomer)->allows('view', $record))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('view', $calculation))->toBeTrue()
        ->and(Gate::forUser($otherCustomer)->allows('view', $calculation))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $record))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $calculation))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $record))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('update', $calculation))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('delete', $calculation))->toBeFalse();
});

it('keeps public roles limited to the two locked values', function () {
    expect(UserRole::cases())->toHaveCount(2)
        ->and(array_column(UserRole::cases(), 'value'))->toBe(['customer', 'admin']);
});
