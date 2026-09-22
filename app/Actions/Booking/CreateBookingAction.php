<?php

namespace App\Actions\Booking;

use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Exceptions\Booking\BookingSlotUnavailableException;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopSetting;
use App\Services\Booking\BookingAvailabilityService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateBookingAction
{
    public function __construct(private readonly BookingAvailabilityService $availabilityService) {}

    public function execute(
        User $user,
        Vehicle $vehicle,
        DateTimeInterface|string $scheduledAt,
        ?string $complaint = null,
        ?int $recommendationCalculationId = null,
    ): Booking {
        Gate::forUser($user)->authorize('create', Booking::class);
        Gate::forUser($user)->authorize('view', $vehicle);

        return DB::transaction(function () use (
            $user,
            $vehicle,
            $scheduledAt,
            $complaint,
            $recommendationCalculationId,
        ): Booking {
            $settings = WorkshopSetting::query()->orderBy('id')->lockForUpdate()->firstOrFail();
            $schedule = $scheduledAt instanceof DateTimeInterface
                ? CarbonImmutable::instance($scheduledAt)->setTimezone($settings->timezone)
                : CarbonImmutable::parse($scheduledAt, $settings->timezone);

            if ($schedule->lte(CarbonImmutable::now($settings->timezone))) {
                throw new BookingSlotUnavailableException('Jadwal booking harus berada di masa depan.');
            }

            $availability = $this->availabilityService->check($schedule, settings: $settings);

            if (! $availability->isAvailable) {
                throw new BookingSlotUnavailableException(
                    $availability->reason ?: 'Slot booking tidak tersedia.',
                );
            }

            $calculationId = null;

            if ($recommendationCalculationId !== null) {
                $calculationId = FuzzyCalculation::query()
                    ->whereKey($recommendationCalculationId)
                    ->where('vehicle_id', $vehicle->id)
                    ->value('id');
            }

            $nextSequence = ((int) Booking::query()->max('id')) + 1;
            $booking = Booking::query()->create([
                'booking_code' => sprintf('BK-%s-%04d', $schedule->format('Y'), $nextSequence),
                'vehicle_id' => $vehicle->id,
                'recommendation_calculation_id' => $calculationId,
                'scheduled_at' => $schedule,
                'duration_minutes' => $settings->slot_duration_minutes,
                'status' => BookingStatus::Pending,
                'complaint' => filled($complaint) ? trim((string) $complaint) : null,
            ]);
            $booking->events()->create([
                'event_type' => BookingEventType::Created,
                'actor_user_id' => $user->id,
                'new_status' => BookingStatus::Pending,
                'new_scheduled_at' => $schedule,
                'note' => 'Booking dibuat oleh customer.',
            ]);

            return $booking->load(['vehicle', 'events']);
        }, attempts: 3);
    }
}
