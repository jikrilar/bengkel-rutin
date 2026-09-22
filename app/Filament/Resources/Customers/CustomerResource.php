<?php

namespace App\Filament\Resources\Customers;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $modelLabel = 'customer';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', UserRole::Customer);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')->schema([
                TextEntry::make('name')->label('Nama'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('phone')->label('Telepon'),
                TextEntry::make('created_at')->label('Terdaftar')->dateTime('d M Y, H:i'),
            ])->columns(2),
            Section::make('Kendaraan')->schema([
                RepeatableEntry::make('vehicles')->label('')->schema([
                    TextEntry::make('name')->label('Nama kendaraan'),
                    TextEntry::make('plate_number')->label('Nomor polisi'),
                    TextEntry::make('brand')->label('Merek / model')->formatStateUsing(fn ($state, $record) => $state.' '.$record->model),
                    TextEntry::make('serviceProfile.name')->label('Profil servis'),
                ])->columns(2),
            ])->collapsible(),
            Section::make('Booking aktif')->schema([
                RepeatableEntry::make('activeBookings')->label('')->schema([
                    TextEntry::make('booking_code')->label('Kode'),
                    TextEntry::make('vehicle.name')->label('Kendaraan'),
                    TextEntry::make('scheduled_at')->label('Jadwal')->dateTime('d M Y, H:i'),
                    TextEntry::make('status')->label('Status')->formatStateUsing(fn (BookingStatus $state) => $state->label())->badge(),
                ])->columns(2),
            ])->collapsible(),
            Section::make('Riwayat servis')->schema([
                RepeatableEntry::make('serviceRecords')->label('')->schema([
                    TextEntry::make('service_code')->label('Kode'),
                    TextEntry::make('vehicle.name')->label('Kendaraan'),
                    TextEntry::make('service_date')->label('Tanggal')->dateTime('d M Y'),
                    TextEntry::make('total_cost')->label('Biaya')->money('IDR'),
                ])->columns(2),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('phone')->label('Telepon')->searchable(),
            TextColumn::make('vehicles_count')->label('Kendaraan')->counts('vehicles'),
            TextColumn::make('created_at')->label('Terdaftar')->date('d M Y')->sortable(),
        ])->recordActions([ViewAction::make()]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
