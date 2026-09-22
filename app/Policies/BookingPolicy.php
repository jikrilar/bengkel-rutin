<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role !== UserRole::Admin) {
            return null;
        }

        return in_array($ability, ['delete', 'forceDelete', 'restore'], true) ? false : true;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $booking->vehicle->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $booking->vehicle->user_id === $user->id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}
