<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodaysBookings extends TableWidget
{
    protected static ?string $heading = 'Booking Hari Ini';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Booking::query()
                ->with(['vehicle.user'])
                ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
                ->orderBy('scheduled_at'))
            ->columns([
                TextColumn::make('scheduled_at')->label('Waktu')->time('H.i')->sortable(),
                TextColumn::make('booking_code')->label('Kode')->searchable(),
                TextColumn::make('vehicle.user.name')->label('Customer'),
                TextColumn::make('vehicle.name')->label('Kendaraan')->description(fn (Booking $record) => $record->vehicle->plate_number),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (BookingStatus $state) => $state->label())->badge(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada booking hari ini')
            ->emptyStateDescription('Booking pada tanggal hari ini akan tampil di sini.');
    }
}
