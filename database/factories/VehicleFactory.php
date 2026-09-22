<?php

namespace Database\Factories;

use App\Enums\BaselineSource;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vehicle> */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_profile_id' => ServiceProfile::factory(),
            'name' => fake()->randomElement(['Kendaraan Harian', 'Mobil Keluarga', 'Motor Utama']),
            'plate_number' => fake()->unique()->regexify('[A-Z]{1,2} [0-9]{4} [A-Z]{2,3}'),
            'brand' => fake()->randomElement(['Honda', 'Toyota', 'Yamaha', 'Suzuki']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2010, (int) now()->format('Y')),
            'baseline_service_date' => fake()->dateTimeBetween('-5 months', '-1 month'),
            'baseline_odometer' => fake()->numberBetween(1000, 80000),
            'baseline_source' => BaselineSource::CustomerInput,
            'baseline_service_record_id' => null,
        ];
    }
}
