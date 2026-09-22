<?php

use App\Enums\BookingStatus;
use App\Enums\OdometerSource;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\ServiceProfiles\ServiceProfileResource;
use App\Filament\Resources\ServiceRecords\ServiceRecordResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Booking;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;

it('renders all core admin operational resource lists and details', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['name' => 'Customer Operasional']);
    $profile = ServiceProfile::factory()->create(['name' => 'Profil Operasional']);
    $vehicle = Vehicle::factory()->for($customer)->for($profile)->create(['name' => 'Mobil Operasional']);
    OdometerLog::factory()->for($vehicle)->create(['source' => OdometerSource::Initial]);
    $booking = Booking::factory()->for($vehicle)->create(['status' => BookingStatus::InService]);
    $record = ServiceRecord::factory()->for($vehicle)->for($booking)->for($admin, 'completedBy')->create();

    $this->actingAs($admin)->get(CustomerResource::getUrl('index'))->assertOk()->assertSee('Customer Operasional');
    $this->actingAs($admin)->get(CustomerResource::getUrl('view', ['record' => $customer]))->assertOk()->assertSee('Mobil Operasional');
    $this->actingAs($admin)->get(VehicleResource::getUrl('index'))->assertOk()->assertSee('Mobil Operasional');
    $this->actingAs($admin)->get(VehicleResource::getUrl('view', ['record' => $vehicle]))->assertOk()->assertSee($vehicle->plate_number);
    $this->actingAs($admin)->get(ServiceRecordResource::getUrl('index'))->assertOk()->assertSee($record->service_code);
    $this->actingAs($admin)->get(ServiceRecordResource::getUrl('view', ['record' => $record]))->assertOk()->assertSee($record->service_code);
    $this->actingAs($admin)->get(ServiceProfileResource::getUrl('index'))->assertOk()->assertSee('Profil Operasional');
    $this->actingAs($admin)->get(ServiceProfileResource::getUrl('create'))->assertOk()->assertSee('Buat Profil Servis');
    $this->actingAs($admin)->get(BookingResource::getUrl('view', ['record' => $booking]))->assertOk()->assertSee('Selesaikan Servis');
});
