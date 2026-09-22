<?php

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\RescheduleBookingAction;
use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Exceptions\Booking\BookingSlotUnavailableException;
use App\Exceptions\Booking\InvalidBookingTransitionException;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopSetting;
use App\Services\Booking\BookingAvailabilityService;
use Carbon\CarbonImmutable;
use Database\Seeders\OperatingHourSeeder;
use Database\Seeders\WorkshopSettingSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 07:00:00');
    $this->seed([WorkshopSettingSeeder::class, OperatingHourSeeder::class]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

function t06Vehicle(?User $owner = null): Vehicle
{
    return Vehicle::factory()->for($owner ?? User::factory())->create();
}

it('reports available capacity and marks full slots', function () {
    $settings = WorkshopSetting::query()->sole();
    $vehicle = t06Vehicle();

    Booking::factory()->count(3)->for($vehicle)->sequence(
        ['status' => BookingStatus::Pending],
        ['status' => BookingStatus::Confirmed],
        ['status' => BookingStatus::InService],
    )->create([
        'scheduled_at' => '2026-09-23 08:00:00',
        'duration_minutes' => $settings->slot_duration_minutes,
    ]);

    $slots = app(BookingAvailabilityService::class)->slotsForDate('2026-09-23');

    expect($slots[0]->state)->toBe('full')
        ->and($slots[0]->isAvailable)->toBeFalse()
        ->and($slots[0]->remainingCapacity)->toBe(0)
        ->and($slots[1]->state)->toBe('available')
        ->and($slots[1]->remainingCapacity)->toBe($settings->slot_capacity);
});

it('counts pending and confirmed but releases cancelled and completed capacity', function () {
    $vehicle = t06Vehicle();
    $statuses = [
        BookingStatus::Pending,
        BookingStatus::Confirmed,
        BookingStatus::Cancelled,
        BookingStatus::Completed,
    ];

    foreach ($statuses as $index => $status) {
        Booking::factory()->for($vehicle)->create([
            'booking_code' => sprintf('BK-2026-%04d', $index + 1),
            'scheduled_at' => '2026-09-23 08:00:00',
            'status' => $status,
        ]);
    }

    $slot = app(BookingAvailabilityService::class)->check('2026-09-23 08:00:00');

    expect($slot->isAvailable)->toBeTrue()
        ->and($slot->state)->toBe('limited')
        ->and($slot->remainingCapacity)->toBe(1);
});

it('creates a pending booking and its created event', function () {
    $customer = User::factory()->create();
    $vehicle = t06Vehicle($customer);

    $booking = app(CreateBookingAction::class)->execute(
        $customer,
        $vehicle,
        '2026-09-23 08:00:00',
        'Bunyi saat pengereman.',
    );

    expect($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->booking_code)->toMatch('/^BK-2026-\d{4}$/')
        ->and($booking->complaint)->toBe('Bunyi saat pengereman.')
        ->and($booking->events)->toHaveCount(1)
        ->and($booking->events->first()->event_type)->toBe(BookingEventType::Created)
        ->and($booking->events->first()->new_status)->toBe(BookingStatus::Pending);
});

it('rejects closed days and closed exceptions on server recheck', function () {
    $customer = User::factory()->create();
    $vehicle = t06Vehicle($customer);

    expect(fn () => app(CreateBookingAction::class)->execute(
        $customer,
        $vehicle,
        '2026-09-27 08:00:00',
    ))->toThrow(BookingSlotUnavailableException::class);
});

it('rejects booking another customers vehicle', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $vehicle = t06Vehicle($owner);

    expect(fn () => app(CreateBookingAction::class)->execute(
        $other,
        $vehicle,
        '2026-09-23 08:00:00',
    ))->toThrow(AuthorizationException::class);

    expect(Booking::query()->count())->toBe(0);
});

it('rechecks stale availability and prevents capacity overflow', function () {
    $settings = WorkshopSetting::query()->sole();
    $customer = User::factory()->create();
    $vehicle = t06Vehicle($customer);
    $service = app(BookingAvailabilityService::class);
    $action = app(CreateBookingAction::class);

    expect($service->check('2026-09-23 08:00:00')->isAvailable)->toBeTrue();

    for ($index = 0; $index < $settings->slot_capacity; $index++) {
        $action->execute($customer, $vehicle, '2026-09-23 08:00:00');
    }

    expect(fn () => $action->execute(
        $customer,
        $vehicle,
        '2026-09-23 08:00:00',
    ))->toThrow(BookingSlotUnavailableException::class)
        ->and(Booking::query()->where('scheduled_at', '2026-09-23 08:00:00')->count())
        ->toBe($settings->slot_capacity);
});

it('allows admin to confirm, reschedule, and cancel while persisting timeline events', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $booking = app(CreateBookingAction::class)->execute(
        $customer,
        t06Vehicle($customer),
        '2026-09-23 08:00:00',
    );

    app(ConfirmBookingAction::class)->execute($admin, $booking, 'Slot dikonfirmasi.');
    app(RescheduleBookingAction::class)->execute(
        $admin,
        $booking->fresh(),
        '2026-09-24 10:00:00',
        'Permintaan customer.',
    );
    $cancelled = app(CancelBookingAction::class)->execute(
        $admin,
        $booking->fresh(),
        'Bengkel tutup lebih awal.',
    );

    expect($cancelled->status)->toBe(BookingStatus::Cancelled)
        ->and($cancelled->scheduled_at->format('Y-m-d H:i'))->toBe('2026-09-24 10:00')
        ->and($cancelled->cancellation_reason)->toBe('Bengkel tutup lebih awal.')
        ->and($cancelled->events()->orderBy('id')->pluck('event_type')->all())->toBe([
            BookingEventType::Created,
            BookingEventType::Confirmed,
            BookingEventType::Rescheduled,
            BookingEventType::Cancelled,
        ]);
});

it('rejects invalid transitions', function () {
    $admin = User::factory()->admin()->create();
    $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);

    expect(fn () => app(ConfirmBookingAction::class)->execute($admin, $booking))
        ->toThrow(InvalidBookingTransitionException::class);
});

it('rechecks capacity before reschedule and leaves the original schedule intact', function () {
    $settings = WorkshopSetting::query()->sole();
    $admin = User::factory()->admin()->create();
    $booking = Booking::factory()->create([
        'scheduled_at' => '2026-09-23 09:00:00',
        'status' => BookingStatus::Pending,
    ]);

    Booking::factory()->count($settings->slot_capacity)->create([
        'scheduled_at' => '2026-09-24 10:00:00',
        'status' => BookingStatus::Confirmed,
    ]);

    expect(fn () => app(RescheduleBookingAction::class)->execute(
        $admin,
        $booking,
        '2026-09-24 10:00:00',
    ))->toThrow(BookingSlotUnavailableException::class);

    expect($booking->fresh()->scheduled_at->format('Y-m-d H:i'))->toBe('2026-09-23 09:00')
        ->and($booking->events()->count())->toBe(0);
});
