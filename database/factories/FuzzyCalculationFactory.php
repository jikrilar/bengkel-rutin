<?php

namespace Database\Factories;

use App\Enums\CalculationTrigger;
use App\Enums\RecommendationStatus;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyConfig;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FuzzyCalculation> */
class FuzzyCalculationFactory extends Factory
{
    public function definition(): array
    {
        $calculatedAt = fake()->dateTimeBetween('-30 days');
        $baselineDate = fake()->dateTimeBetween('-5 months', '-2 months');

        return [
            'vehicle_id' => Vehicle::factory(),
            'fuzzy_config_id' => FuzzyConfig::factory(),
            'trigger_type' => CalculationTrigger::OdometerUpdated,
            'interval_km_snapshot' => 4000,
            'interval_days_snapshot' => 120,
            'current_odometer' => 13000,
            'baseline_odometer' => 10000,
            'baseline_service_date' => $baselineDate,
            'km_since_service' => 3000,
            'days_since_service' => 90,
            'average_daily_km' => 33.33,
            'baseline_daily_usage' => 33.33,
            'progress_km' => 75,
            'progress_time' => 75,
            'usage_intensity' => 100,
            'km_safe_mu' => 0.75,
            'km_approaching_mu' => 0.25,
            'km_critical_mu' => 0,
            'time_safe_mu' => 0.75,
            'time_approaching_mu' => 0.25,
            'time_critical_mu' => 0,
            'usage_normal_mu' => 0.5,
            'usage_intensive_mu' => 0.5,
            'score' => 35,
            'fuzzy_status' => RecommendationStatus::NotNeeded,
            'estimated_due_by_km' => now()->addDays(30),
            'estimated_due_by_time' => now()->addDays(30),
            'estimated_due_date' => now()->addDays(30),
            'recommended_from_date' => now()->addDays(31),
            'recommended_to_date' => null,
            'recommended_date' => now()->addDays(31),
            'final_status' => RecommendationStatus::NotNeeded,
            'guard_applied' => false,
            'guard_reason' => null,
            'calculated_at' => $calculatedAt,
        ];
    }
}
