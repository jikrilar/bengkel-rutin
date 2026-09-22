<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FuzzyCalculation;
use App\Models\User;

class FuzzyCalculationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role !== UserRole::Admin) {
            return null;
        }

        return in_array($ability, ['view', 'viewAny'], true);
    }

    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function view(User $user, FuzzyCalculation $fuzzyCalculation): bool
    {
        return $fuzzyCalculation->vehicle->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, FuzzyCalculation $fuzzyCalculation): bool
    {
        return false;
    }

    public function delete(User $user, FuzzyCalculation $fuzzyCalculation): bool
    {
        return false;
    }

    public function restore(User $user, FuzzyCalculation $fuzzyCalculation): bool
    {
        return false;
    }

    public function forceDelete(User $user, FuzzyCalculation $fuzzyCalculation): bool
    {
        return false;
    }
}
