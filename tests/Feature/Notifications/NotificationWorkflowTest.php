<?php

use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\RescheduleBookingAction;
use App\Enums\BaselineSource;
use App\Enums\BookingStatus;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Events\ServiceCompleted;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyConfig;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingRescheduledNotification;
use App\Notifications\ServiceApproachingNotification;
use App\Notifications\ServiceCompletedNotification;
use App\Notifications\ServiceUrgentNotification;
use App\Services\Notification\NotificationDeliveryService;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Database\Seeders\OperatingHourSeeder;
use Database\Seeders\WorkshopSettingSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-23 10:00:00');
    Cache::flush();
    $this->seed([
        FuzzyConfigSeeder::class,
        FuzzyRuleSeeder::class,
        WorkshopSettingSeeder::class,
        OperatingHourSeeder::class,
    ]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

function t08RecommendationVehicle(): Vehicle
{
    $customer = User::factory()->create();
    $profile = ServiceProfile::factory()->create([
        'interval_km' => 4000,
        'interval_days' => 120,
    ]);
    $vehicle = Vehicle::factory()->for($customer)->for($profile)->create([
        'baseline_service_date' => '2026-06-25',
        'baseline_odometer' => 10000,
        'baseline_source' => BaselineSource::CustomerInput,
    ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 10000,
        'recorded_at' => '2026-06-25 10:00:00',
        'source' => OdometerSource::Initial,
        'recorded_by' => $customer->id,
    ]);

    return $vehicle;
}

it('sends approaching and urgent notifications only when recommendation status increases', function () {
    Notification::fake();
    $vehicle = t08RecommendationVehicle();
    $service = app(RecommendationService::class);

    FuzzyCalculation::factory()
        ->for($vehicle)
        ->for(FuzzyConfig::query()->active()->sole(), 'fuzzyConfig')
        ->create([
            'score' => 25,
            'fuzzy_status' => RecommendationStatus::NotNeeded,
            'final_status' => RecommendationStatus::NotNeeded,
            'calculated_at' => '2026-09-22 10:00:00',
        ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 13000,
        'recorded_at' => '2026-09-23 09:00:00',
        'source' => OdometerSource::CustomerUpdate,
        'recorded_by' => $vehicle->user_id,
    ]);
    $service->calculateAndPersist($vehicle->fresh(), CalculationTrigger::OdometerUpdated);

    expect($vehicle->fresh()->latestFuzzyCalculation->final_status)
        ->toBe(RecommendationStatus::Approaching);
    Notification::assertSentTo($vehicle->user, ServiceApproachingNotification::class);
    $approachingCount = Notification::sent($vehicle->user, ServiceApproachingNotification::class)->count();

    $service->calculateAndPersist($vehicle->fresh(), CalculationTrigger::ManualRecalculate);
    expect(Notification::sent($vehicle->user, ServiceApproachingNotification::class))->toHaveCount($approachingCount);

    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 14200,
        'recorded_at' => '2026-09-23 09:30:00',
        'source' => OdometerSource::CustomerUpdate,
        'recorded_by' => $vehicle->user_id,
    ]);
    $service->calculateAndPersist($vehicle->fresh(), CalculationTrigger::OdometerUpdated);

    Notification::assertSentTo($vehicle->user, ServiceUrgentNotification::class);
});

it('queues booking confirmation and reschedule notifications from committed actions', function () {
    Notification::fake();
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $booking = Booking::factory()->for($vehicle)->create([
        'status' => BookingStatus::Pending,
        'scheduled_at' => '2026-09-24 08:00:00',
    ]);

    app(ConfirmBookingAction::class)->execute($admin, $booking);
    app(RescheduleBookingAction::class)->execute($admin, $booking->fresh(), '2026-09-25 09:00:00');

    Notification::assertSentTo($customer, BookingConfirmedNotification::class);
    Notification::assertSentTo($customer, BookingRescheduledNotification::class);
});

it('does not roll back a confirmed booking when notification queueing fails', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $booking = Booking::factory()->for(Vehicle::factory()->for($customer))->create([
        'status' => BookingStatus::Pending,
    ]);
    $this->mock(NotificationDeliveryService::class)
        ->shouldReceive('sendOnce')
        ->once()
        ->andThrow(new RuntimeException('SMTP side effect unavailable.'));

    $confirmed = app(ConfirmBookingAction::class)->execute($admin, $booking);

    expect($confirmed->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
});

it('queues a service completed notification from the domain event', function () {
    Notification::fake();
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $record = ServiceRecord::factory()->for($vehicle)->for($admin, 'completedBy')->create();

    ServiceCompleted::dispatch($record);

    Notification::assertSentTo($customer, ServiceCompletedNotification::class);
});

it('uses queued notification classes for email and database side effects', function () {
    expect(new ServiceApproachingNotification(new FuzzyCalculation, 'queue-check'))
        ->toBeInstanceOf(ShouldQueue::class);
});

it('shows only the authenticated customers notifications and safely marks them read', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $own = $customer->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => ServiceApproachingNotification::class,
        'data' => [
            'title' => 'Servis kendaraan mulai mendekat',
            'message' => 'Periksa rekomendasi kendaraan.',
            'target_url' => route('recommendations.index', absolute: false),
            'dedup_key' => 'own-notification',
        ],
    ]);
    $foreign = $other->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => ServiceUrgentNotification::class,
        'data' => [
            'title' => 'Notifikasi milik customer lain',
            'message' => 'Tidak boleh terlihat.',
            'target_url' => route('recommendations.index', absolute: false),
            'dedup_key' => 'foreign-notification',
        ],
    ]);

    $this->actingAs($customer)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Servis kendaraan mulai mendekat')
        ->assertDontSee('Notifikasi milik customer lain');
    $this->actingAs($customer)->patch(route('notifications.read', $own))->assertRedirect();
    expect($own->fresh()->read_at)->not->toBeNull();
    $this->actingAs($customer)->get(route('notifications.open', $own))
        ->assertRedirect(route('recommendations.index', absolute: false));
    $this->actingAs($customer)->patch(route('notifications.read', $foreign))->assertNotFound();
});
