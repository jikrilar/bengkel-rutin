<?php

use App\Enums\BaselineSource;
use App\Enums\BookingStatus;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyConfig;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ServiceApproachingNotification;
use App\Notifications\UpcomingBookingNotification;
use App\Services\Notification\ServiceRecommendationReminderService;
use App\Services\Notification\UpcomingBookingReminderService;
use App\Services\Recommendation\DailyRecommendationService;
use Carbon\CarbonImmutable;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-23 08:00:00');
    Cache::flush();
    $this->seed([FuzzyConfigSeeder::class, FuzzyRuleSeeder::class]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

function t08ScheduledVehicle(bool $completeBaseline = true): Vehicle
{
    $customer = User::factory()->create();
    $profile = ServiceProfile::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->for($profile)->create($completeBaseline ? [
        'baseline_service_date' => '2026-08-01',
        'baseline_odometer' => 10000,
        'baseline_source' => BaselineSource::CustomerInput,
    ] : [
        'baseline_service_date' => null,
        'baseline_odometer' => null,
        'baseline_source' => null,
    ]);
    OdometerLog::factory()->for($vehicle)->create([
        'odometer' => 11000,
        'recorded_at' => '2026-09-22 08:00:00',
        'source' => OdometerSource::CustomerUpdate,
        'recorded_by' => $customer->id,
    ]);

    return $vehicle;
}

it('runs daily recalculation once per eligible vehicle and skips incomplete baselines', function () {
    $eligible = t08ScheduledVehicle();
    $incomplete = t08ScheduledVehicle(false);
    $service = app(DailyRecommendationService::class);
    $now = CarbonImmutable::now();

    $first = $service->run($now);
    $second = $service->run($now);

    expect($first)->toMatchArray(['processed' => 1, 'failed' => 0])
        ->and($second)->toMatchArray(['processed' => 0, 'skipped' => 1, 'failed' => 0])
        ->and(FuzzyCalculation::query()
            ->where('vehicle_id', $eligible->id)
            ->where('trigger_type', CalculationTrigger::DailyScheduler)
            ->whereDate('calculated_at', '2026-09-23')
            ->count())->toBe(1)
        ->and(FuzzyCalculation::query()->where('vehicle_id', $incomplete->id)->count())->toBe(0);
});

it('sends the proper status notification when daily recalculation increases urgency', function () {
    Notification::fake();
    $vehicle = t08ScheduledVehicle();
    FuzzyCalculation::factory()
        ->for($vehicle)
        ->for(FuzzyConfig::query()->active()->sole(), 'fuzzyConfig')
        ->create([
            'score' => 20,
            'fuzzy_status' => RecommendationStatus::NotNeeded,
            'final_status' => RecommendationStatus::NotNeeded,
            'calculated_at' => '2026-09-22 08:00:00',
        ]);

    app(DailyRecommendationService::class)->run();

    Notification::assertSentTo($vehicle->user, ServiceApproachingNotification::class);
});

it('deduplicates periodic service recommendation reminders per service cycle and status', function () {
    Notification::fake();
    $vehicle = t08ScheduledVehicle();
    FuzzyCalculation::factory()
        ->for($vehicle)
        ->for(FuzzyConfig::query()->active()->sole(), 'fuzzyConfig')
        ->create([
            'score' => 55,
            'fuzzy_status' => RecommendationStatus::Approaching,
            'final_status' => RecommendationStatus::Approaching,
            'calculated_at' => '2026-09-23 07:00:00',
        ]);

    $service = app(ServiceRecommendationReminderService::class);
    expect($service->send())->toBe(1)
        ->and($service->send())->toBe(0);
    expect(Notification::sent($vehicle->user, ServiceApproachingNotification::class))->toHaveCount(1);
});

it('sends an H-1 reminder once and excludes cancelled or completed bookings', function () {
    Notification::fake();
    $customer = User::factory()->create();
    $vehicle = Vehicle::factory()->for($customer)->create();
    $eligible = Booking::factory()->for($vehicle)->create([
        'scheduled_at' => '2026-09-24 09:00:00',
        'status' => BookingStatus::Confirmed,
    ]);
    Booking::factory()->for($vehicle)->create([
        'scheduled_at' => '2026-09-24 10:00:00',
        'status' => BookingStatus::Cancelled,
    ]);
    Booking::factory()->for($vehicle)->create([
        'scheduled_at' => '2026-09-24 11:00:00',
        'status' => BookingStatus::Completed,
    ]);

    $service = app(UpcomingBookingReminderService::class);
    expect($service->send())->toBe(1)
        ->and($service->send())->toBe(0);

    Notification::assertSentTo(
        $customer,
        UpcomingBookingNotification::class,
        fn (UpcomingBookingNotification $notification) => $notification->booking->is($eligible),
    );
    expect(Notification::sent($customer, UpcomingBookingNotification::class))->toHaveCount(1);
});

it('registers all operational schedules without overlapping execution', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('recommendations:recalculate-daily')
        ->expectsOutputToContain('recommendations:send-reminders')
        ->expectsOutputToContain('bookings:send-upcoming-reminders')
        ->assertSuccessful();
});
