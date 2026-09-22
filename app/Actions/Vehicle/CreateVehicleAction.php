<?php

namespace App\Actions\Vehicle;

use App\Enums\BaselineSource;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateVehicleAction
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    /** @param array<string, mixed> $data */
    public function execute(User $user, array $data): Vehicle
    {
        Gate::forUser($user)->authorize('create', Vehicle::class);

        return DB::transaction(function () use ($user, $data): Vehicle {
            $normalizedPlate = Vehicle::normalizePlateNumber((string) $data['plate_number']);

            if (Vehicle::withTrashed()->where('plate_number_normalized', $normalizedPlate)->exists()) {
                throw ValidationException::withMessages([
                    'plate_number' => 'Nomor polisi ini sudah terdaftar.',
                ]);
            }

            $profile = ServiceProfile::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->firstOrFail();
            $hasBaseline = (bool) ($data['knows_last_service'] ?? false);

            $vehicle = Vehicle::query()->create([
                ...Arr::only($data, ['name', 'brand', 'model', 'year', 'plate_number']),
                'user_id' => $user->id,
                'service_profile_id' => $profile->id,
                'baseline_service_date' => $hasBaseline ? $data['last_service_date'] : null,
                'baseline_odometer' => $hasBaseline ? $data['last_service_odometer'] : null,
                'baseline_source' => $hasBaseline ? BaselineSource::CustomerInput : null,
            ]);

            $vehicle->odometerLogs()->create([
                'odometer' => $data['current_odometer'],
                'recorded_at' => now(),
                'source' => OdometerSource::Initial,
                'recorded_by' => $user->id,
            ]);

            if ($hasBaseline) {
                $this->recommendationService->calculateAndPersist(
                    $vehicle,
                    CalculationTrigger::VehicleCreated,
                    now(),
                );
            }

            return $vehicle->fresh([
                'serviceProfile',
                'latestOdometer',
                'latestFuzzyCalculation',
            ]);
        }, attempts: 3);
    }
}
