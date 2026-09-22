<?php

namespace Database\Factories;

use App\Models\FuzzyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FuzzyConfig> */
class FuzzyConfigFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => fake()->unique()->numberBetween(2, 100000),
            'progress_safe_end' => 70,
            'progress_approaching_peak' => 90,
            'progress_critical_full' => 100,
            'usage_normal_full_until' => 80,
            'usage_intensive_full_from' => 120,
            'is_active' => false,
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }
}
