<?php

namespace Database\Factories;

use App\Models\ServiceProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceProfile> */
class ServiceProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'interval_km' => fake()->randomElement([3000, 4000, 5000, 10000]),
            'interval_days' => fake()->randomElement([90, 120, 180, 365]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
