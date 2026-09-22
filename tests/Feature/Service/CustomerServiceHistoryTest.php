<?php

use App\Models\Booking;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;

it('shows only the authenticated customers service history and detail', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $otherVehicle = Vehicle::factory()->for($other)->create();
    $booking = Booking::factory()->for($vehicle)->create();
    $record = ServiceRecord::factory()->for($vehicle)->for($booking)->create([
        'service_type' => 'Servis Berkala',
        'work_performed' => 'Ganti oli dan inspeksi rem.',
        'total_cost' => 350000,
    ]);
    $otherRecord = ServiceRecord::factory()->for($otherVehicle)->create();

    $this->actingAs($customer)->get(route('service-history.index'))
        ->assertOk()
        ->assertSee($record->service_code)
        ->assertDontSee($otherRecord->service_code);

    $this->actingAs($customer)->get(route('service-history.show', $record))
        ->assertOk()
        ->assertSee('Servis Berkala')
        ->assertSee('Ganti oli dan inspeksi rem.')
        ->assertSee('Rp350.000');
});

it('forbids cross-customer service record access and admin routes for customers', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $record = ServiceRecord::factory()->for(Vehicle::factory()->for($owner))->create();

    $this->actingAs($other)->get(route('service-history.show', $record))->assertForbidden();
    $this->actingAs($other)->get('/admin/bookings')->assertForbidden();
});
