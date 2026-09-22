<?php

namespace App\Filament\Resources\ServiceRecords;

use App\Enums\UserRole;
use App\Filament\Resources\ServiceRecords\Pages\ListServiceRecords;
use App\Filament\Resources\ServiceRecords\Pages\ViewServiceRecord;
use App\Models\ServiceRecord;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ServiceRecordResource extends Resource
{
    protected static ?string $model = ServiceRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Riwayat Servis';

    protected static ?string $modelLabel = 'catatan servis';

    protected static ?string $recordTitleAttribute = 'service_code';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Servis')->schema([
                TextEntry::make('service_code')->label('Kode servis'),
                TextEntry::make('service_date')->label('Tanggal servis')->dateTime('d M Y, H:i'),
                TextEntry::make('service_type')->label('Jenis servis'),
                TextEntry::make('odometer')->label('Odometer')->numeric()->suffix(' km'),
                TextEntry::make('total_cost')->label('Total biaya')->money('IDR'),
                TextEntry::make('completedBy.name')->label('Diselesaikan oleh'),
            ])->columns(2),
            Section::make('Customer dan kendaraan')->schema([
                TextEntry::make('vehicle.user.name')->label('Customer'),
                TextEntry::make('vehicle.user.email')->label('Email customer'),
                TextEntry::make('vehicle.name')->label('Kendaraan'),
                TextEntry::make('vehicle.plate_number')->label('Nomor polisi'),
                TextEntry::make('booking.booking_code')->label('Referensi booking')->placeholder('Tanpa booking'),
            ])->columns(2),
            Section::make('Pekerjaan')->schema([
                TextEntry::make('complaint')->label('Keluhan')->placeholder('Tidak ada')->columnSpanFull(),
                TextEntry::make('work_performed')->label('Pekerjaan yang dilakukan')->columnSpanFull(),
                TextEntry::make('notes')->label('Catatan')->placeholder('Tidak ada')->columnSpanFull(),
                TextEntry::make('correction_reason')->label('Alasan koreksi')->visible(fn (ServiceRecord $record) => filled($record->correction_reason))->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('service_date', 'desc')->columns([
            TextColumn::make('service_code')->label('Kode')->searchable()->sortable(),
            TextColumn::make('service_date')->label('Tanggal')->dateTime('d M Y, H:i')->sortable(),
            TextColumn::make('vehicle.name')->label('Kendaraan')->description(fn (ServiceRecord $record) => $record->vehicle->plate_number)->searchable(),
            TextColumn::make('vehicle.user.name')->label('Customer')->searchable(),
            TextColumn::make('service_type')->label('Jenis servis')->searchable(),
            TextColumn::make('total_cost')->label('Biaya')->money('IDR')->sortable(),
        ])->filters([
            Filter::make('period')->schema([
                DatePicker::make('from')->label('Dari tanggal'),
                DatePicker::make('until')->label('Sampai tanggal'),
            ])->query(fn (Builder $query, array $data) => $query
                ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('service_date', '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('service_date', '<=', $date))),
            Filter::make('customer')->schema([
                Select::make('customer_id')->label('Customer')->searchable()->options(fn () => User::query()->where('role', UserRole::Customer)->orderBy('name')->pluck('name', 'id')),
            ])->query(fn (Builder $query, array $data) => $query->when($data['customer_id'] ?? null, fn (Builder $query, $id) => $query->whereHas('vehicle', fn (Builder $query) => $query->where('user_id', $id)))),
        ])->recordActions([ViewAction::make()]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRecords::route('/'),
            'view' => ViewServiceRecord::route('/{record}'),
        ];
    }
}
