<?php

use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyConfig;
use App\Models\OdometerLog;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;

it('connects vehicle ownership and service profile relationships', function () {
    $vehicle = Vehicle::factory()->create();

    expect($vehicle->user)->toBeInstanceOf(User::class)
        ->and($vehicle->serviceProfile->vehicles->modelKeys())->toContain($vehicle->id)
        ->and($vehicle->user->vehicles->modelKeys())->toContain($vehicle->id);
});

it('resolves the latest odometer by business timestamp', function () {
    $vehicle = Vehicle::factory()->create();

    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 15000,
        'recorded_at' => now()->subDay(),
    ]);
    $latest = OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 15100,
        'recorded_at' => now(),
    ]);

    expect($vehicle->fresh()->latestOdometer->is($latest))->toBeTrue();
});

it('resolves the latest fuzzy calculation and current active booking', function () {
    $vehicle = Vehicle::factory()->create();
    $config = FuzzyConfig::factory()->create();

    FuzzyCalculation::factory()->for($vehicle)->create([
        'fuzzy_config_id' => $config->id,
        'calculated_at' => now()->subHour(),
    ]);
    $latestCalculation = FuzzyCalculation::factory()->for($vehicle)->create([
        'fuzzy_config_id' => $config->id,
        'calculated_at' => now(),
    ]);

    $activeBooking = Booking::factory()->for($vehicle)->create([
        'status' => BookingStatus::Confirmed,
        'scheduled_at' => now()->addDay(),
    ]);
    Booking::factory()->for($vehicle)->create([
        'status' => BookingStatus::Completed,
        'scheduled_at' => now()->addDays(2),
    ]);

    $vehicle = $vehicle->fresh();

    expect($vehicle->latestFuzzyCalculation->is($latestCalculation))->toBeTrue()
        ->and($vehicle->activeBooking->is($activeBooking))->toBeTrue()
        ->and($vehicle->bookings()->active()->count())->toBe(1);
});

it('connects booking history and service records without deleting history', function () {
    $vehicle = Vehicle::factory()->create();
    $booking = Booking::factory()->for($vehicle)->create();
    $actor = User::factory()->admin()->create();
    $event = BookingEvent::query()->create([
        'booking_id' => $booking->id,
        'event_type' => BookingEventType::Created,
        'actor_user_id' => $actor->id,
        'new_status' => BookingStatus::Pending,
    ]);
    $record = ServiceRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'booking_id' => $booking->id,
        'completed_by' => $actor->id,
    ]);

    $vehicle->update(['baseline_service_record_id' => $record->id]);

    expect($booking->fresh()->events->first()->is($event))->toBeTrue()
        ->and($booking->serviceRecord->is($record))->toBeTrue()
        ->and($record->completedBy->is($actor))->toBeTrue()
        ->and($vehicle->fresh()->baselineServiceRecord->is($record))->toBeTrue();
});
