<?php

use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\StartServiceAction;
use App\Actions\Service\CompleteServiceAction;
use App\Enums\BaselineSource;
use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Events\ServiceCompleted;
use App\Exceptions\Booking\InvalidBookingTransitionException;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\OdometerLog;
use App\Models\ServiceProfile;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 12:00:00');
    $this->seed([FuzzyConfigSeeder::class, FuzzyRuleSeeder::class]);
    $this->profile = ServiceProfile::factory()->create([
        'interval_km' => 4000,
        'interval_days' => 120,
        'is_active' => true,
    ]);
    $this->customer = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->vehicle = Vehicle::factory()->for($this->customer)->for($this->profile)->create([
        'baseline_service_date' => '2026-06-01',
        'baseline_odometer' => 10000,
        'baseline_source' => BaselineSource::CustomerInput,
    ]);
    OdometerLog::factory()->for($this->vehicle)->create([
        'odometer' => 12000,
        'recorded_at' => '2026-09-21 10:00:00',
        'source' => OdometerSource::CustomerUpdate,
        'recorded_by' => $this->customer->id,
    ]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

function t07ServiceData(array $overrides = []): array
{
    return array_merge([
        'service_date' => '2026-09-22 11:00:00',
        'odometer' => 12100,
        'service_type' => 'Servis Rutin',
        'complaint' => 'Rem terasa kurang pakem.',
        'work_performed' => 'Ganti oli dan pemeriksaan sistem pengereman.',
        'notes' => 'Kampas rem masih layak.',
        'total_cost' => 475000,
    ], $overrides);
}

it('transitions confirmed booking to in service and stores started event', function () {
    $booking = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::Confirmed]);

    $started = app(StartServiceAction::class)->execute($this->admin, $booking);

    expect($started->status)->toBe(BookingStatus::InService)
        ->and($started->events()->sole()->event_type)->toBe(BookingEventType::Started);
});

it('completes service transaction and starts a new recommendation cycle', function () {
    Event::fake([ServiceCompleted::class]);
    app(RecommendationService::class)->calculateAndPersist(
        $this->vehicle,
        CalculationTrigger::ManualRecalculate,
        CarbonImmutable::parse('2026-09-22 10:00:00'),
    );
    $previousCalculationId = $this->vehicle->fresh()->latestFuzzyCalculation->id;
    $booking = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::InService]);

    $record = app(CompleteServiceAction::class)->execute($this->admin, $booking, t07ServiceData());
    $vehicle = $this->vehicle->fresh();
    $calculation = $vehicle->latestFuzzyCalculation;

    expect($record->booking_id)->toBe($booking->id)
        ->and($record->completed_by)->toBe($this->admin->id)
        ->and($record->total_cost)->toBe('475000.00')
        ->and($booking->fresh()->status)->toBe(BookingStatus::Completed)
        ->and($booking->events()->where('event_type', BookingEventType::Completed)->exists())->toBeTrue()
        ->and($vehicle->baseline_service_date->toDateString())->toBe('2026-09-22')
        ->and($vehicle->baseline_odometer)->toBe(12100)
        ->and($vehicle->baseline_source)->toBe(BaselineSource::ServiceRecord)
        ->and($vehicle->baseline_service_record_id)->toBe($record->id)
        ->and($vehicle->latestOdometer->source)->toBe(OdometerSource::Service)
        ->and($vehicle->latestOdometer->odometer)->toBe(12100)
        ->and($calculation)->not->toBeNull()
        ->and($calculation->trigger_type)->toBe(CalculationTrigger::ServiceCompleted)
        ->and((float) $calculation->progress_km)->toBe(0.0)
        ->and((float) $calculation->progress_time)->toBe(0.0)
        ->and($calculation->fuzzy_status)->toBe(RecommendationStatus::NotNeeded)
        ->and($calculation->final_status)->toBe(RecommendationStatus::NotNeeded)
        ->and($calculation->baseline_odometer)->toBe(12100)
        ->and($calculation->ruleResults)->not->toBeEmpty()
        ->and(FuzzyCalculation::query()->whereKey($previousCalculationId)->exists())->toBeTrue()
        ->and($vehicle->fuzzyCalculations()->count())->toBe(2);

    Event::assertDispatched(ServiceCompleted::class, fn (ServiceCompleted $event) => $event->serviceRecord->is($record));
});

it('rejects start and completion from invalid booking states', function () {
    $pending = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::Pending]);
    $cancelled = Booking::factory()->for($this->vehicle)->cancelled()->create();
    $completed = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::Completed]);
    $inService = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::InService]);

    expect(fn () => app(StartServiceAction::class)->execute($this->admin, $pending))
        ->toThrow(InvalidBookingTransitionException::class)
        ->and(fn () => app(StartServiceAction::class)->execute($this->admin, $cancelled))
        ->toThrow(InvalidBookingTransitionException::class)
        ->and(fn () => app(StartServiceAction::class)->execute($this->admin, $completed))
        ->toThrow(InvalidBookingTransitionException::class)
        ->and(fn () => app(ConfirmBookingAction::class)->execute($this->admin, $inService))
        ->toThrow(InvalidBookingTransitionException::class)
        ->and(fn () => app(CompleteServiceAction::class)->execute($this->admin, $pending, t07ServiceData()))
        ->toThrow(ValidationException::class);
});

it('rejects a lower service odometer and negative total cost', function () {
    $booking = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::InService]);

    expect(fn () => app(CompleteServiceAction::class)->execute(
        $this->admin,
        $booking,
        t07ServiceData(['odometer' => 11999]),
    ))->toThrow(ValidationException::class)
        ->and(fn () => app(CompleteServiceAction::class)->execute(
            $this->admin,
            $booking,
            t07ServiceData(['total_cost' => -1]),
        ))->toThrow(ValidationException::class);

    expect(ServiceRecord::query()->count())->toBe(0)
        ->and($booking->fresh()->status)->toBe(BookingStatus::InService);
});

it('rolls back all critical writes when recommendation persistence fails', function () {
    $booking = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::InService]);
    $baselineDate = $this->vehicle->baseline_service_date->toDateString();
    $baselineOdometer = $this->vehicle->baseline_odometer;
    $odometerCount = OdometerLog::query()->count();
    $this->mock(RecommendationService::class)
        ->shouldReceive('calculateAndPersist')
        ->once()
        ->andThrow(new RuntimeException('Simulated persistence failure.'));

    expect(fn () => app(CompleteServiceAction::class)->execute(
        $this->admin,
        $booking,
        t07ServiceData(),
    ))->toThrow(RuntimeException::class);

    expect(ServiceRecord::query()->count())->toBe(0)
        ->and(OdometerLog::query()->count())->toBe($odometerCount)
        ->and(FuzzyCalculation::query()->count())->toBe(0)
        ->and($booking->fresh()->status)->toBe(BookingStatus::InService)
        ->and($booking->events()->count())->toBe(0)
        ->and($this->vehicle->fresh()->baseline_service_date->toDateString())->toBe($baselineDate)
        ->and($this->vehicle->fresh()->baseline_odometer)->toBe($baselineOdometer)
        ->and($this->vehicle->fresh()->baseline_service_record_id)->toBeNull();
});

it('forbids customers from starting or completing services', function () {
    $confirmed = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::Confirmed]);
    $inService = Booking::factory()->for($this->vehicle)->create(['status' => BookingStatus::InService]);

    expect(fn () => app(StartServiceAction::class)->execute($this->customer, $confirmed))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => app(CompleteServiceAction::class)->execute($this->customer, $inService, t07ServiceData()))
        ->toThrow(AuthorizationException::class);
});
