<?php

use App\Enums\BookingStatus;
use App\Enums\RecommendationStatus;
use App\Filament\Pages\Reports;
use App\Filament\Widgets\OperationsOverview;
use App\Filament\Widgets\TodaysBookings;
use App\Filament\Widgets\UrgentVehicles;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Admin\AdminDashboardService;
use App\Services\Reporting\ReportService;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-23 12:00:00');
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('calculates period report metrics from booking and service domain data', function () {
    $admin = User::factory()->admin()->create();
    $customerA = User::factory()->create();
    $customerB = User::factory()->create();
    $vehicleA = Vehicle::factory()->for($customerA)->create();
    $vehicleB = Vehicle::factory()->for($customerB)->create();

    Booking::factory()->count(2)->for($vehicleA)->sequence(
        ['scheduled_at' => '2026-09-05 09:00:00'],
        ['scheduled_at' => '2026-09-20 10:00:00'],
    )->create();
    Booking::factory()->for($vehicleB)->create(['scheduled_at' => '2026-10-01 09:00:00']);
    ServiceRecord::factory()->for($vehicleA)->for($admin, 'completedBy')->create([
        'service_date' => '2026-09-10 11:00:00',
        'total_cost' => 250000,
    ]);
    ServiceRecord::factory()->for($vehicleB)->for($admin, 'completedBy')->create([
        'service_date' => '2026-09-18 11:00:00',
        'total_cost' => 400000,
    ]);
    ServiceRecord::factory()->for($vehicleA)->for($admin, 'completedBy')->create([
        'service_date' => '2026-08-31 11:00:00',
        'total_cost' => 900000,
    ]);

    $report = app(ReportService::class)->forPeriod('2026-09-01', '2026-09-30');

    expect($report['total_bookings'])->toBe(2)
        ->and($report['completed_services'])->toBe(2)
        ->and($report['service_revenue'])->toBe(650000.0)
        ->and($report['unique_customers'])->toBe(2)
        ->and($report['transactions'])->toHaveCount(2);
});

it('returns actionable dashboard values from current database state', function () {
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    Booking::factory()->for($vehicle)->create([
        'scheduled_at' => '2026-09-23 09:00:00',
        'status' => BookingStatus::Pending,
    ]);
    Booking::factory()->for($vehicle)->create([
        'scheduled_at' => '2026-09-24 09:00:00',
        'status' => BookingStatus::InService,
    ]);
    FuzzyCalculation::factory()->for($vehicle)->create([
        'score' => 85,
        'fuzzy_status' => RecommendationStatus::Urgent,
        'final_status' => RecommendationStatus::Urgent,
        'calculated_at' => '2026-09-23 08:00:00',
    ]);

    $service = app(AdminDashboardService::class);

    expect($service->metrics())->toBe([
        'today_bookings' => 1,
        'pending_confirmations' => 1,
        'in_service' => 1,
        'urgent_vehicles' => 1,
    ])->and($service->todayBookings())->toHaveCount(1)
        ->and($service->urgentVehicles())->toHaveCount(1);
});

it('limits urgent dashboard vehicles in score order without loading every match', function () {
    $customer = User::factory()->create();
    $vehicles = Vehicle::factory()->count(12)->for($customer)->create();

    foreach ($vehicles as $index => $vehicle) {
        FuzzyCalculation::factory()->for($vehicle)->create([
            'score' => 70 + $index,
            'fuzzy_status' => RecommendationStatus::Urgent,
            'final_status' => RecommendationStatus::Urgent,
            'calculated_at' => '2026-09-23 09:00:00',
        ]);
    }

    FuzzyCalculation::factory()->for($vehicles->first())->create([
        'score' => 100,
        'fuzzy_status' => RecommendationStatus::Urgent,
        'final_status' => RecommendationStatus::Urgent,
        'calculated_at' => '2026-09-22 09:00:00',
    ]);

    $topVehicles = app(AdminDashboardService::class)->urgentVehicles(3);

    expect($topVehicles->modelKeys())->toBe([
        $vehicles[11]->id,
        $vehicles[10]->id,
        $vehicles[9]->id,
    ]);
});

it('renders reports and final dashboard for admin but blocks customers', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $this->actingAs($admin)->get(Reports::getUrl())
        ->assertOk()
        ->assertSee('Total booking')
        ->assertSee('Transaksi servis');
    $this->actingAs($admin)->get('/admin')->assertOk();
    Livewire::actingAs($admin)->test(OperationsOverview::class)->assertSee('Booking Hari Ini');
    Livewire::actingAs($admin)->test(TodaysBookings::class)->assertSee('Booking Hari Ini');
    Livewire::actingAs($admin)->test(UrgentVehicles::class)->assertSee('Kendaraan Segera Servis');

    $this->actingAs($customer)->get(Reports::getUrl())->assertForbidden();
    $this->actingAs($customer)->get('/admin')->assertForbidden();
});
