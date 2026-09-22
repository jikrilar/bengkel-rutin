<?php

namespace App\Actions\Booking;

use App\Enums\UserRole;
use App\Exceptions\Booking\BookingSlotUnavailableException;
use App\Models\Booking;
use App\Models\User;
use App\Models\WorkshopSetting;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Booking\BookingTransitionService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RescheduleBookingAction
{
    public function __construct(
        private readonly BookingAvailabilityService $availabilityService,
        private readonly BookingTransitionService $transitionService,
    ) {}

    public function execute(
        User $admin,
        Booking $booking,
        DateTimeInterface|string $scheduledAt,
        ?string $note = null,
    ): Booking {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat menjadwalkan ulang booking.');
        }

        Gate::forUser($admin)->authorize('update', $booking);

        return DB::transaction(function () use ($admin, $booking, $scheduledAt, $note): Booking {
            $settings = WorkshopSetting::query()->orderBy('id')->lockForUpdate()->firstOrFail();
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $schedule = $scheduledAt instanceof DateTimeInterface
                ? CarbonImmutable::instance($scheduledAt)->setTimezone($settings->timezone)
                : CarbonImmutable::parse($scheduledAt, $settings->timezone);

            if ($schedule->lte(CarbonImmutable::now($settings->timezone))) {
                throw new BookingSlotUnavailableException('Jadwal booking harus berada di masa depan.');
            }

            $availability = $this->availabilityService->check(
                $schedule,
                excludeBookingId: $lockedBooking->id,
                settings: $settings,
            );

            if (! $availability->isAvailable) {
                throw new BookingSlotUnavailableException(
                    $availability->reason ?: 'Slot booking tidak tersedia.',
                );
            }

            return $this->transitionService->reschedule(
                $lockedBooking,
                $schedule,
                $admin,
                filled($note) ? trim((string) $note) : 'Jadwal diubah oleh admin.',
            );
        }, attempts: 3);
    }
}
