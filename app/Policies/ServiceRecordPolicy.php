<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceRecord;
use App\Models\User;

class ServiceRecordPolicy
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

    public function view(User $user, ServiceRecord $serviceRecord): bool
    {
        return $serviceRecord->vehicle->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ServiceRecord $serviceRecord): bool
    {
        return false;
    }

    public function delete(User $user, ServiceRecord $serviceRecord): bool
    {
        return false;
    }

    public function restore(User $user, ServiceRecord $serviceRecord): bool
    {
        return false;
    }

    public function forceDelete(User $user, ServiceRecord $serviceRecord): bool
    {
        return false;
    }
}
