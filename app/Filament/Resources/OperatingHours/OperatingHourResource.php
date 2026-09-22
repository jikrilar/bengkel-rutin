<?php

namespace App\Filament\Resources\OperatingHours;

use App\Filament\Resources\OperatingHours\Pages\EditOperatingHour;
use App\Filament\Resources\OperatingHours\Pages\ListOperatingHours;
use App\Models\OperatingHour;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OperatingHourResource extends Resource
{
    protected static ?string $model = OperatingHour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal Bengkel';

    protected static ?string $navigationLabel = 'Jam Operasional';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('workshop_setting_id'),
            Section::make('Jam operasional harian')->schema([
                Select::make('day_of_week')->label('Hari')->options(collect(range(1, 7))->mapWithKeys(fn (int $day) => [$day => OperatingHour::dayLabel($day)]))->disabled()->dehydrated(),
                Toggle::make('is_open')->label('Bengkel buka')->live(),
                TimePicker::make('open_time')->label('Jam buka')->seconds(false)->required(fn ($get) => $get('is_open'))->visible(fn ($get) => $get('is_open')),
                TimePicker::make('close_time')->label('Jam tutup')->seconds(false)->required(fn ($get) => $get('is_open'))->after('open_time')->visible(fn ($get) => $get('is_open')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('day_of_week')->columns([
            TextColumn::make('day_of_week')->label('Hari')->formatStateUsing(fn (int $state) => OperatingHour::dayLabel($state)),
            IconColumn::make('is_open')->label('Buka')->boolean(),
            TextColumn::make('open_time')->label('Jam buka')->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '—'),
            TextColumn::make('close_time')->label('Jam tutup')->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '—'),
        ])->recordActions([EditAction::make()])->paginated(false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperatingHours::route('/'),
            'edit' => EditOperatingHour::route('/{record}/edit'),
        ];
    }
}
