<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_code' => sprintf(
                'BK-%s-%06d',
                now()->format('Y'),
                fake()->unique()->numberBetween(1, 999999),
            ),
            'vehicle_id' => Vehicle::factory(),
            'recommendation_calculation_id' => null,
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'duration_minutes' => 60,
            'status' => BookingStatus::Pending,
            'complaint' => fake()->optional()->sentence(),
            'cancellation_reason' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Cancelled,
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
