<?php

namespace App\Actions\Vehicle;

use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Exceptions\Vehicle\InvalidOdometerException;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateOdometerAction
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    /** @param array{odometer: int, recorded_at: string} $data */
    public function execute(User $user, Vehicle $vehicle, array $data): OdometerLog
    {
        Gate::forUser($user)->authorize('update', $vehicle);

        return DB::transaction(function () use ($user, $vehicle, $data): OdometerLog {
            $lockedVehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicle->id);
            Gate::forUser($user)->authorize('update', $lockedVehicle);

            $latest = $lockedVehicle->odometerLogs()
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
            $recordedAt = CarbonImmutable::parse($data['recorded_at'], config('app.timezone'));

            if ($latest !== null && $data['odometer'] < $latest->odometer) {
                throw ValidationException::withMessages([
                    'odometer' => sprintf('Odometer baru minimal %s km.', number_format($latest->odometer, 0, ',', '.')),
                ]);
            }

            if ($latest !== null && $recordedAt->lt($latest->recorded_at)) {
                throw ValidationException::withMessages([
                    'recorded_at' => 'Waktu pencatatan tidak boleh lebih awal dari catatan terakhir.',
                ]);
            }

            if ($latest === null) {
                throw new InvalidOdometerException('Vehicle does not have an initial odometer log.');
            }

            $log = $lockedVehicle->odometerLogs()->create([
                'odometer' => $data['odometer'],
                'recorded_at' => $recordedAt,
                'source' => OdometerSource::CustomerUpdate,
                'recorded_by' => $user->id,
            ]);

            if (
                $lockedVehicle->baseline_service_date !== null
                && $lockedVehicle->baseline_odometer !== null
            ) {
                $lockedVehicle->unsetRelation('latestOdometer');
                $this->recommendationService->calculateAndPersist(
                    $lockedVehicle,
                    CalculationTrigger::OdometerUpdated,
                    $recordedAt,
                );
            }

            return $log;
        }, attempts: 3);
    }
}
