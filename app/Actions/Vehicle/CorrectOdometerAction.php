<?php

namespace App\Actions\Vehicle;

use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\UserRole;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CorrectOdometerAction
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    public function execute(User $admin, Vehicle $vehicle, int $correctedValue, string $reason): OdometerLog
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat mengoreksi odometer.');
        }

        Gate::forUser($admin)->authorize('update', $vehicle);

        return DB::transaction(function () use ($admin, $vehicle, $correctedValue, $reason): OdometerLog {
            $lockedVehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicle->id);
            $latest = $lockedVehicle->odometerLogs()
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($latest === null || $correctedValue < $latest->odometer) {
                throw ValidationException::withMessages([
                    'corrected_value' => sprintf(
                        'Nilai koreksi minimal %s km agar histori tetap non-decreasing.',
                        number_format($latest?->odometer ?? 0, 0, ',', '.'),
                    ),
                ]);
            }

            if (mb_strlen(trim($reason)) < 5) {
                throw ValidationException::withMessages([
                    'reason' => 'Alasan koreksi wajib diisi minimal 5 karakter.',
                ]);
            }

            $recordedAt = CarbonImmutable::now(config('app.timezone'));
            if ($recordedAt->lte($latest->recorded_at)) {
                $recordedAt = CarbonImmutable::instance($latest->recorded_at)->addSecond();
            }

            $log = $lockedVehicle->odometerLogs()->create([
                'odometer' => $correctedValue,
                'recorded_at' => $recordedAt,
                'source' => OdometerSource::AdminCorrection,
                'recorded_by' => $admin->id,
                'correction_reason' => trim($reason),
            ]);

            if ($lockedVehicle->baseline_service_date !== null && $lockedVehicle->baseline_odometer !== null) {
                $lockedVehicle->unsetRelation('latestOdometer');
                $this->recommendationService->calculateAndPersist(
                    $lockedVehicle,
                    CalculationTrigger::ManualRecalculate,
                    $recordedAt,
                );
            }

            return $log;
        }, attempts: 3);
    }
}
