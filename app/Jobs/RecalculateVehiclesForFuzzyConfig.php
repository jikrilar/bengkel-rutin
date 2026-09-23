<?php

namespace App\Jobs;

use App\Enums\CalculationTrigger;
use App\Models\FuzzyConfig;
use App\Models\Vehicle;
use App\Services\Recommendation\RecommendationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecalculateVehiclesForFuzzyConfig implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public readonly int $fuzzyConfigId) {}

    public function handle(RecommendationService $recommendations): void
    {
        if (! FuzzyConfig::query()->whereKey($this->fuzzyConfigId)->where('is_active', true)->exists()) {
            return;
        }

        Vehicle::query()
            ->whereNotNull('baseline_service_date')
            ->whereNotNull('baseline_odometer')
            ->whereHas('serviceProfile')
            ->whereHas('latestOdometer')
            ->orderBy('id')
            ->chunkById(100, function ($vehicles) use ($recommendations): void {
                foreach ($vehicles as $vehicle) {
                    try {
                        $recommendations->calculateAndPersist($vehicle, CalculationTrigger::FuzzyConfigChanged);
                    } catch (Throwable $exception) {
                        Log::error('Fuzzy config recalculation failed.', [
                            'vehicle_id' => $vehicle->id,
                            'fuzzy_config_id' => $this->fuzzyConfigId,
                            'exception' => $exception,
                        ]);
                    }
                }
            });
    }
}
