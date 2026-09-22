<?php

namespace Database\Factories;

use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceRecord> */
class ServiceRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_code' => sprintf(
                'SRV-%s-%06d',
                now()->format('Y'),
                fake()->unique()->numberBetween(1, 999999),
            ),
            'vehicle_id' => Vehicle::factory(),
            'booking_id' => null,
            'service_date' => fake()->dateTimeBetween('-1 year'),
            'odometer' => fake()->numberBetween(1000, 150000),
            'service_type' => fake()->randomElement(['Servis Rutin', 'Ganti Oli', 'Tune Up']),
            'complaint' => fake()->optional()->sentence(),
            'work_performed' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'total_cost' => fake()->randomFloat(2, 0, 5000000),
            'completed_by' => User::factory()->admin(),
            'correction_reason' => null,
        ];
    }
}
