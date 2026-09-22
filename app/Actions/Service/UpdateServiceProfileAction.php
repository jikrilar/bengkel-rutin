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
use Illuminate\Support\Facades\Validator;

class UpdateServiceProfileAction
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    /** @param array{name: string, interval_km: int, interval_days: int, description?: string|null, is_active?: bool} $data */
    public function execute(User $admin, ServiceProfile $profile, array $data): ServiceProfile
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat mengubah profil servis.');
        }

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'interval_km' => ['required', 'integer', 'min:1'],
            'interval_days' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ])->validate();

        return DB::transaction(function () use ($profile, $validated): ServiceProfile {
            $lockedProfile = ServiceProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $intervalChanged = $lockedProfile->interval_km !== (int) $validated['interval_km']
                || $lockedProfile->interval_days !== (int) $validated['interval_days'];
            $lockedProfile->update($validated);

            if ($intervalChanged) {
                $lockedProfile->vehicles()
                    ->whereNotNull('baseline_service_date')
                    ->whereNotNull('baseline_odometer')
                    ->with('latestOdometer')
                    ->each(function (Vehicle $vehicle): void {
                        $vehicle->unsetRelation('serviceProfile');
                        $this->recommendationService->calculateAndPersist(
                            $vehicle,
                            CalculationTrigger::ServiceProfileChanged,
                        );
                    });
            }

            return $lockedProfile->refresh();
        }, attempts: 3);
    }
}
