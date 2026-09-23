<?php

namespace App\Filament\Resources\FuzzyConfigs;

use App\Filament\Resources\FuzzyConfigs\Pages\ListFuzzyConfigs;
use App\Models\FuzzyConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class FuzzyConfigResource extends Resource
{
    protected static ?string $model = FuzzyConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Konfigurasi Fuzzy';

    protected static ?string $modelLabel = 'konfigurasi fuzzy';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('version', 'desc')
            ->columns([
                TextColumn::make('version')->label('Versi')->prefix('v')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('progress_safe_end')->label('Aman')->suffix('%'),
                TextColumn::make('progress_approaching_peak')->label('Puncak mendekati')->suffix('%'),
                TextColumn::make('progress_critical_full')->label('Kritis penuh')->suffix('%'),
                TextColumn::make('usage_normal_full_until')->label('Normal penuh')->suffix('%'),
                TextColumn::make('usage_intensive_full_from')->label('Intensif penuh')->suffix('%'),
                TextColumn::make('creator.name')->label('Dibuat oleh')->placeholder('Seeder'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y, H:i')->sortable(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFuzzyConfigs::route('/'),
        ];
    }
}
