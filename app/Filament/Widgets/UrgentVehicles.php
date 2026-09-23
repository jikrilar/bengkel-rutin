<?php

namespace App\Filament\Widgets;

use App\Enums\RecommendationStatus;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UrgentVehicles extends TableWidget
{
    protected static ?string $heading = 'Kendaraan Segera Servis';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Vehicle::query()
                ->with(['user', 'latestFuzzyCalculation'])
                ->whereHas('latestFuzzyCalculation', fn (Builder $query) => $query->where('final_status', RecommendationStatus::Urgent)))
            ->columns([
                TextColumn::make('name')->label('Kendaraan')->description(fn (Vehicle $record) => $record->plate_number)->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('latestFuzzyCalculation.score')->label('Skor')->suffix(' / 100')->numeric(2)->sortable(),
                TextColumn::make('latestFuzzyCalculation.recommended_date')->label('Rekomendasi')->date('d M Y')->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (Vehicle $record) => VehicleResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('Tidak ada kendaraan mendesak')
            ->emptyStateDescription('Kendaraan dengan status Segera Servis akan tampil di sini.');
    }
}
