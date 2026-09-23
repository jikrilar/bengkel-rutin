<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $metrics = app(AdminDashboardService::class)->metrics();

        return [
            Stat::make('Booking Hari Ini', $metrics['today_bookings'])
                ->description('Kunjungan terjadwal hari ini')
                ->url(BookingResource::getUrl('index')),
            Stat::make('Menunggu Konfirmasi', $metrics['pending_confirmations'])
                ->description('Booking yang perlu ditindaklanjuti')
                ->color($metrics['pending_confirmations'] > 0 ? 'warning' : 'gray')
                ->url(BookingResource::getUrl('index')),
            Stat::make('Sedang Servis', $metrics['in_service'])
                ->description('Kendaraan dalam pengerjaan')
                ->color($metrics['in_service'] > 0 ? 'warning' : 'gray')
                ->url(BookingResource::getUrl('index')),
            Stat::make('Kendaraan Segera Servis', $metrics['urgent_vehicles'])
                ->description('Rekomendasi berstatus mendesak')
                ->color($metrics['urgent_vehicles'] > 0 ? 'danger' : 'gray')
                ->url(VehicleResource::getUrl('index')),
        ];
    }
}
