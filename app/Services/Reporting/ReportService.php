<?php

namespace App\Services\Reporting;

use App\Models\Booking;
use App\Models\ServiceRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * @return array{
     *   start: CarbonImmutable,
     *   end: CarbonImmutable,
     *   total_bookings: int,
     *   completed_services: int,
     *   service_revenue: float,
     *   unique_customers: int,
     *   transactions: Collection<int, ServiceRecord>
     * }
     */
    public function forPeriod(string $startDate, string $endDate): array
    {
        $start = CarbonImmutable::parse($startDate, config('app.timezone'))->startOfDay();
        $end = CarbonImmutable::parse($endDate, config('app.timezone'))->endOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages([
                'endDate' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            ]);
        }

        $serviceQuery = ServiceRecord::query()->whereBetween('service_date', [$start, $end]);

        return [
            'start' => $start,
            'end' => $end,
            'total_bookings' => Booking::query()->whereBetween('scheduled_at', [$start, $end])->count(),
            'completed_services' => (clone $serviceQuery)->count(),
            'service_revenue' => (float) (clone $serviceQuery)->sum('total_cost'),
            'unique_customers' => (clone $serviceQuery)
                ->join('vehicles', 'service_records.vehicle_id', '=', 'vehicles.id')
                ->distinct('vehicles.user_id')
                ->count('vehicles.user_id'),
            'transactions' => (clone $serviceQuery)
                ->with(['vehicle.user', 'booking'])
                ->orderByDesc('service_date')
                ->limit(100)
                ->get(),
        ];
    }
}
