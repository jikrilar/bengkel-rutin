<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Services\Booking\BookingTransitionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CancelBookingAction
{
    public function __construct(private readonly BookingTransitionService $transitionService) {}

    public function execute(User $actor, Booking $booking, string $reason): Booking
    {
        Gate::forUser($actor)->authorize('update', $booking);

        return DB::transaction(function () use ($actor, $booking, $reason): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            Gate::forUser($actor)->authorize('update', $lockedBooking);

            return $this->transitionService->transition(
                $lockedBooking,
                BookingStatus::Cancelled,
                $actor,
                trim($reason),
            );
        }, attempts: 3);
    }
}
