<?php

use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Livewire\Bookings\CreateBooking;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopSetting;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 07:00:00');
    $this->withSession(['_token' => 'test-token']);
    $this->settings = WorkshopSetting::query()->create([
        'workshop_name' => 'Bengkel Test',
        'address' => 'Jl. Test',
        'phone' => '021000',
        'email' => 'test@example.com',
        'timezone' => 'Asia/Jakarta',
        'slot_duration_minutes' => 60,
        'slot_capacity' => 2,
    ]);
    foreach (range(1, 7) as $day) {
        OperatingHour::query()->create([
            'workshop_setting_id' => $this->settings->id,
            'day_of_week' => $day,
            'is_open' => $day < 7,
            'open_time' => $day < 7 ? '08:00:00' : null,
            'close_time' => $day < 7 ? '17:00:00' : null,
        ]);
    }
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('renders the customer booking flow and creates a pending booking through livewire', function () {
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();

    $this->actingAs($customer)->get(route('bookings.create', ['vehicle' => $vehicle->id]))
        ->assertOk()
        ->assertSee('Buat jadwal servis');

    Livewire::actingAs($customer)
        ->test(CreateBooking::class, ['vehicleId' => $vehicle->id])
        ->set('date', '2026-09-23')
        ->set('time', '08:00')
        ->set('complaint', 'Rem terasa kurang pakem.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect();

    $booking = Booking::query()->sole();
    expect($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->vehicle_id)->toBe($vehicle->id)
        ->and($booking->events()->where('event_type', BookingEventType::Created)->exists())->toBeTrue();
});

it('shows active and historical bookings separately', function () {
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $active = Booking::factory()->for($vehicle)->create(['status' => BookingStatus::Pending, 'scheduled_at' => '2026-09-23 08:00:00']);
    $history = Booking::factory()->for($vehicle)->cancelled()->create(['scheduled_at' => '2026-09-24 09:00:00']);

    $this->actingAs($customer)->get(route('bookings.index'))
        ->assertOk()
        ->assertSee($active->booking_code)
        ->assertSee($history->status->label())
        ->assertSee('Jadwal aktif')
        ->assertSee('Riwayat');
});

it('lets an owner view and cancel a pending booking with an event', function () {
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $booking = Booking::factory()->for($vehicle)->create(['status' => BookingStatus::Pending]);

    $this->actingAs($customer)->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertSee($booking->booking_code);

    $this->actingAs($customer)->post(route('bookings.cancel', $booking), [
        '_token' => 'test-token',
        'reason' => 'Ada agenda mendadak.',
    ])->assertRedirect(route('bookings.show', $booking));

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->events()->where('event_type', BookingEventType::Cancelled)->exists())->toBeTrue();
});

it('forbids viewing or cancelling another customers booking', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $booking = Booking::factory()->for(Vehicle::factory()->for($owner))->create();

    $this->actingAs($other)->get(route('bookings.show', $booking))->assertForbidden();
    $this->actingAs($other)->post(route('bookings.cancel', $booking), [
        '_token' => 'test-token',
        'reason' => 'Tidak berhak membatalkan.',
    ])->assertForbidden();
});

it('rejects a vehicle owned by another customer in the livewire flow', function () {
    $customer = User::factory()->create();
    $otherVehicle = Vehicle::factory()->for(User::factory())->create();

    Livewire::actingAs($customer)
        ->test(CreateBooking::class)
        ->set('vehicleId', $otherVehicle->id)
        ->set('date', '2026-09-23')
        ->set('time', '08:00')
        ->call('submit')
        ->assertHasErrors('vehicleId');

    expect(Booking::query()->count())->toBe(0);
});
