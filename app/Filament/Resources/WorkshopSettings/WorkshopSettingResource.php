<?php

namespace App\Filament\Resources\WorkshopSettings;

use App\Filament\Resources\WorkshopSettings\Pages\EditWorkshopSetting;
use App\Filament\Resources\WorkshopSettings\Pages\ListWorkshopSettings;
use App\Models\WorkshopSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WorkshopSettingResource extends Resource
{
    protected static ?string $model = WorkshopSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal Bengkel';

    protected static ?string $navigationLabel = 'Pengaturan Bengkel';

    protected static ?string $modelLabel = 'pengaturan bengkel';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas bengkel')->schema([
                TextInput::make('workshop_name')->label('Nama bengkel')->required()->maxLength(150),
                Textarea::make('address')->label('Alamat')->required()->rows(3)->columnSpanFull(),
                TextInput::make('phone')->label('Telepon')->tel()->required()->maxLength(30),
                TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                Select::make('timezone')->label('Zona waktu')->options([
                    'Asia/Jakarta' => 'WIB — Asia/Jakarta',
                    'Asia/Makassar' => 'WITA — Asia/Makassar',
                    'Asia/Jayapura' => 'WIT — Asia/Jayapura',
                ])->required(),
            ])->columns(2),
            Section::make('Kapasitas jadwal')->schema([
                TextInput::make('slot_duration_minutes')->label('Durasi slot (menit)')->numeric()->minValue(15)->maxValue(240)->required(),
                TextInput::make('slot_capacity')->label('Kapasitas per slot')->numeric()->minValue(1)->maxValue(100)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workshop_name')->label('Bengkel')->searchable(),
                TextColumn::make('timezone')->label('Zona waktu'),
                TextColumn::make('slot_duration_minutes')->label('Durasi')->suffix(' menit'),
                TextColumn::make('slot_capacity')->label('Kapasitas')->suffix(' kendaraan'),
            ])
            ->recordActions([EditAction::make()])
            ->paginated(false);
    }

    public static function canCreate(): bool
    {
        return ! WorkshopSetting::query()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkshopSettings::route('/'),
            'edit' => EditWorkshopSetting::route('/{record}/edit'),
        ];
    }
}
