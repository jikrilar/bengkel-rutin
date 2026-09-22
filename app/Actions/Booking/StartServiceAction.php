<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use App\Services\Booking\BookingTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StartServiceAction
{
    public function __construct(private readonly BookingTransitionService $transitionService) {}

    public function execute(User $admin, Booking $booking, ?string $note = null): Booking
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat memulai servis.');
        }

        Gate::forUser($admin)->authorize('update', $booking);

        return DB::transaction(function () use ($admin, $booking, $note): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            return $this->transitionService->transition(
                $lockedBooking,
                BookingStatus::InService,
                $admin,
                filled($note) ? trim((string) $note) : 'Pengerjaan servis dimulai.',
            );
        }, attempts: 3);
    }
}
