<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role !== UserRole::Admin) {
            return null;
        }

        return $ability === 'forceDelete' ? false : true;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return false;
    }
}
