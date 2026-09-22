<?php

namespace App\Filament\Resources\ServiceProfiles;

use App\Actions\Service\UpdateServiceProfileAction;
use App\Filament\Resources\ServiceProfiles\Pages\CreateServiceProfile;
use App\Filament\Resources\ServiceProfiles\Pages\EditServiceProfile;
use App\Filament\Resources\ServiceProfiles\Pages\ListServiceProfiles;
use App\Models\ServiceProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ServiceProfileResource extends Resource
{
    protected static ?string $model = ServiceProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $navigationLabel = 'Profil Servis';

    protected static ?string $modelLabel = 'profil servis';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Interval servis')->schema([
                TextInput::make('name')->label('Nama profil')->required()->maxLength(150),
                TextInput::make('interval_km')->label('Interval kilometer')->numeric()->minValue(1)->suffix('km')->required(),
                TextInput::make('interval_days')->label('Interval waktu')->numeric()->minValue(1)->suffix('hari')->required(),
                Toggle::make('is_active')->label('Aktif')->default(true),
                Textarea::make('description')->label('Deskripsi')->maxLength(5000)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('interval_km')->label('Interval kilometer')->numeric()->suffix(' km'),
            TextColumn::make('interval_days')->label('Interval waktu')->suffix(' hari'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('vehicles_count')->label('Digunakan')->counts('vehicles')->suffix(' kendaraan'),
        ])->recordActions([
            EditAction::make(),
            Action::make('deactivate')->label('Nonaktifkan')->icon(Heroicon::OutlinedNoSymbol)->color('warning')
                ->visible(fn (ServiceProfile $record) => $record->is_active)
                ->requiresConfirmation()
                ->action(function (ServiceProfile $record): void {
                    app(UpdateServiceProfileAction::class)->execute(auth()->user(), $record, [
                        'name' => $record->name,
                        'interval_km' => $record->interval_km,
                        'interval_days' => $record->interval_days,
                        'description' => $record->description,
                        'is_active' => false,
                    ]);
                    Notification::make()->success()->title('Profil servis dinonaktifkan.')->send();
                }),
        ]);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceProfiles::route('/'),
            'create' => CreateServiceProfile::route('/create'),
            'edit' => EditServiceProfile::route('/{record}/edit'),
        ];
    }
}
