<?php

namespace App\Services\Recommendation;

use App\Enums\CalculationTrigger;
use App\Exceptions\Recommendation\RecommendationUnavailableException;
use App\Models\FuzzyCalculation;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DailyRecommendationService
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    /** @return array{processed: int, skipped: int, failed: int} */
    public function run(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now(config('app.timezone'));
        $result = ['processed' => 0, 'skipped' => 0, 'failed' => 0];

        Vehicle::query()
            ->whereNotNull('baseline_service_date')
            ->whereNotNull('baseline_odometer')
            ->whereHas('serviceProfile')
            ->whereHas('latestOdometer')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $vehicleId) use (&$result, $now): void {
                try {
                    $created = DB::transaction(function () use ($vehicleId, $now): bool {
                        $vehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicleId);
                        $alreadyCalculated = FuzzyCalculation::query()
                            ->where('vehicle_id', $vehicle->id)
                            ->where('trigger_type', CalculationTrigger::DailyScheduler)
                            ->whereDate('calculated_at', $now->toDateString())
                            ->exists();

                        if ($alreadyCalculated) {
                            return false;
                        }

                        $this->recommendations->calculateAndPersist(
                            $vehicle,
                            CalculationTrigger::DailyScheduler,
                            $now,
                        );

                        return true;
                    }, attempts: 3);

                    $result[$created ? 'processed' : 'skipped']++;
                } catch (RecommendationUnavailableException) {
                    $result['skipped']++;
                } catch (Throwable $exception) {
                    $result['failed']++;
                    Log::error('Daily recommendation recalculation failed.', [
                        'vehicle_id' => $vehicleId,
                        'exception' => $exception,
                    ]);
                }
            });

        return $result;
    }
}
