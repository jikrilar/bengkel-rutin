<?php

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\OperatingHours\OperatingHourResource;
use App\Filament\Resources\ScheduleExceptions\ScheduleExceptionResource;
use App\Filament\Resources\WorkshopSettings\WorkshopSettingResource;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopSetting;

it('allows admins to open booking and workshop management resources', function () {
    $admin = User::factory()->admin()->create();
    $settings = WorkshopSetting::query()->create([
        'workshop_name' => 'Bengkel Test',
        'address' => 'Jl. Test',
        'phone' => '021000',
        'email' => 'test@example.com',
        'timezone' => 'Asia/Jakarta',
        'slot_duration_minutes' => 60,
        'slot_capacity' => 2,
    ]);
    OperatingHour::query()->create([
        'workshop_setting_id' => $settings->id,
        'day_of_week' => 1,
        'is_open' => true,
        'open_time' => '08:00:00',
        'close_time' => '17:00:00',
    ]);
    $booking = Booking::factory()->for(Vehicle::factory()->for(User::factory()))->create();

    $this->actingAs($admin)->get(BookingResource::getUrl('index'))->assertOk()->assertSee($booking->booking_code);
    $this->actingAs($admin)->get(BookingResource::getUrl('view', ['record' => $booking]))->assertOk()->assertSee($booking->booking_code);
    $this->actingAs($admin)->get(WorkshopSettingResource::getUrl('index'))->assertOk()->assertSee('Bengkel Test');
    $this->actingAs($admin)->get(OperatingHourResource::getUrl('index'))->assertOk()->assertSee('Senin');
    $this->actingAs($admin)->get(ScheduleExceptionResource::getUrl('index'))->assertOk();
});
