<?php

namespace App\Services\Admin;

use App\Enums\BookingStatus;
use App\Enums\RecommendationStatus;
use App\Models\Booking;
use App\Models\FuzzyCalculation;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AdminDashboardService
{
    /** @return array{today_bookings: int, pending_confirmations: int, in_service: int, urgent_vehicles: int} */
    public function metrics(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now(config('app.timezone'));

        return [
            'today_bookings' => Booking::query()->whereBetween('scheduled_at', [$now->startOfDay(), $now->endOfDay()])->count(),
            'pending_confirmations' => Booking::query()->where('status', BookingStatus::Pending)->count(),
            'in_service' => Booking::query()->where('status', BookingStatus::InService)->count(),
            'urgent_vehicles' => Vehicle::query()
                ->whereHas('latestFuzzyCalculation', fn ($query) => $query->where('final_status', RecommendationStatus::Urgent))
                ->count(),
        ];
    }

    /** @return Collection<int, Booking> */
    public function todayBookings(?CarbonImmutable $now = null): Collection
    {
        $now ??= CarbonImmutable::now(config('app.timezone'));

        return Booking::query()
            ->with(['vehicle.user'])
            ->whereBetween('scheduled_at', [$now->startOfDay(), $now->endOfDay()])
            ->orderBy('scheduled_at')
            ->get();
    }

    /** @return Collection<int, Vehicle> */
    public function urgentVehicles(int $limit = 10): Collection
    {
        return Vehicle::query()
            ->with(['user', 'latestFuzzyCalculation'])
            ->whereHas('latestFuzzyCalculation', fn ($query) => $query->where('final_status', RecommendationStatus::Urgent))
            ->orderByDesc(
                FuzzyCalculation::query()
                    ->select('score')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->orderByDesc('calculated_at')
                    ->orderByDesc('id')
                    ->limit(1),
            )
            ->limit($limit)
            ->get()
            ->values();
    }
}
