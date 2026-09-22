<?php

namespace App\Actions\Service;

use App\Enums\CalculationTrigger;
use App\Enums\UserRole;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AssignServiceProfileAction
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    public function execute(User $admin, Vehicle $vehicle, ServiceProfile $profile): Vehicle
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat mengganti profil servis.');
        }

        Gate::forUser($admin)->authorize('update', $vehicle);

        if (! $profile->is_active || $profile->trashed()) {
            throw ValidationException::withMessages([
                'service_profile_id' => 'Pilih profil servis yang masih aktif.',
            ]);
        }

        return DB::transaction(function () use ($vehicle, $profile): Vehicle {
            $lockedVehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicle->id);
            $lockedVehicle->update(['service_profile_id' => $profile->id]);

            if ($lockedVehicle->baseline_service_date !== null && $lockedVehicle->baseline_odometer !== null) {
                $lockedVehicle->unsetRelation('serviceProfile');
                $lockedVehicle->unsetRelation('latestOdometer');
                $this->recommendationService->calculateAndPersist(
                    $lockedVehicle,
                    CalculationTrigger::ServiceProfileChanged,
                );
            }

            return $lockedVehicle->refresh()->load('serviceProfile');
        }, attempts: 3);
    }
}
