<?php

use App\Enums\BaselineSource;
use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\FuzzyCalculation;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;

it('casts every locked finite state to its native enum', function () {
    $user = User::factory()->create(['role' => UserRole::Customer]);
    $vehicle = Vehicle::factory()->for($user)->create([
        'baseline_source' => BaselineSource::AdminCorrection,
    ]);
    $odometer = OdometerLog::factory()->for($vehicle)->create([
        'source' => OdometerSource::AdminCorrection,
    ]);
    $booking = Booking::factory()->for($vehicle)->create([
        'status' => BookingStatus::Confirmed,
    ]);
    $event = BookingEvent::query()->create([
        'booking_id' => $booking->id,
        'event_type' => BookingEventType::Confirmed,
        'old_status' => BookingStatus::Pending,
        'new_status' => BookingStatus::Confirmed,
    ]);
    $calculation = FuzzyCalculation::factory()->for($vehicle)->create([
        'trigger_type' => CalculationTrigger::ManualRecalculate,
        'fuzzy_status' => RecommendationStatus::Approaching,
        'final_status' => RecommendationStatus::Urgent,
    ]);

    expect($user->role)->toBe(UserRole::Customer)
        ->and($vehicle->baseline_source)->toBe(BaselineSource::AdminCorrection)
        ->and($odometer->source)->toBe(OdometerSource::AdminCorrection)
        ->and($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($event->event_type)->toBe(BookingEventType::Confirmed)
        ->and($event->old_status)->toBe(BookingStatus::Pending)
        ->and($event->new_status)->toBe(BookingStatus::Confirmed)
        ->and($calculation->trigger_type)->toBe(CalculationTrigger::ManualRecalculate)
        ->and($calculation->fuzzy_status)->toBe(RecommendationStatus::Approaching)
        ->and($calculation->final_status)->toBe(RecommendationStatus::Urgent);
});
