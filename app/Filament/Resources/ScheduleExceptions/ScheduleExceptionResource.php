<?php

namespace App\Filament\Resources\ScheduleExceptions;

use App\Filament\Resources\ScheduleExceptions\Pages\CreateScheduleException;
use App\Filament\Resources\ScheduleExceptions\Pages\EditScheduleException;
use App\Filament\Resources\ScheduleExceptions\Pages\ListScheduleExceptions;
use App\Models\ScheduleException;
use App\Models\WorkshopSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ScheduleExceptionResource extends Resource
{
    protected static ?string $model = ScheduleException::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal Bengkel';

    protected static ?string $navigationLabel = 'Pengecualian Jadwal';

    protected static ?string $modelLabel = 'pengecualian jadwal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tanggal khusus')->schema([
                Hidden::make('workshop_setting_id')->default(fn () => WorkshopSetting::query()->value('id')),
                DatePicker::make('date')->label('Tanggal')->required()->unique(ignoreRecord: true),
                Toggle::make('is_closed')->label('Tutup sepanjang hari')->default(true)->live(),
                TimePicker::make('open_time')->label('Jam buka khusus')->seconds(false)->required(fn ($get) => ! $get('is_closed'))->visible(fn ($get) => ! $get('is_closed')),
                TimePicker::make('close_time')->label('Jam tutup khusus')->seconds(false)->required(fn ($get) => ! $get('is_closed'))->after('open_time')->visible(fn ($get) => ! $get('is_closed')),
                Textarea::make('reason')->label('Alasan')->required()->maxLength(500)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
            IconColumn::make('is_closed')->label('Tutup')->boolean(),
            TextColumn::make('open_time')->label('Jam khusus')->formatStateUsing(fn (?string $state, ScheduleException $record) => $record->is_closed ? 'Tutup penuh' : substr((string) $state, 0, 5).'–'.substr((string) $record->close_time, 0, 5)),
            TextColumn::make('reason')->label('Alasan')->wrap()->searchable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function canDelete(Model $record): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduleExceptions::route('/'),
            'create' => CreateScheduleException::route('/create'),
            'edit' => EditScheduleException::route('/{record}/edit'),
        ];
    }
}
