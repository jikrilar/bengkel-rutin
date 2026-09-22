<?php

namespace Database\Factories;

use App\Enums\OdometerSource;
use App\Models\OdometerLog;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OdometerLog> */
class OdometerLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'odometer' => fake()->numberBetween(1000, 120000),
            'recorded_at' => fake()->dateTimeBetween('-6 months'),
            'source' => OdometerSource::CustomerUpdate,
            'recorded_by' => null,
            'correction_reason' => null,
        ];
    }
}
