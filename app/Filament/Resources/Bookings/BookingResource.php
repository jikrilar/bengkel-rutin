<?php

namespace App\Filament\Resources\Bookings;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\RescheduleBookingAction;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $modelLabel = 'booking';

    protected static ?string $recordTitleAttribute = 'booking_code';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking')->schema([
                TextEntry::make('booking_code')->label('Kode'),
                TextEntry::make('status')->label('Status')->formatStateUsing(fn (BookingStatus $state) => $state->label())->badge(),
                TextEntry::make('scheduled_at')->label('Jadwal')->dateTime('d M Y, H:i'),
                TextEntry::make('duration_minutes')->label('Durasi')->suffix(' menit'),
            ])->columns(2),
            Section::make('Customer dan kendaraan')->schema([
                TextEntry::make('vehicle.user.name')->label('Customer'),
                TextEntry::make('vehicle.user.email')->label('Email'),
                TextEntry::make('vehicle.name')->label('Kendaraan'),
                TextEntry::make('vehicle.plate_number')->label('Nomor polisi'),
                TextEntry::make('complaint')->label('Keluhan / catatan')->placeholder('Tidak ada catatan')->columnSpanFull(),
                TextEntry::make('cancellation_reason')->label('Alasan pembatalan')->visible(fn (Booking $record) => filled($record->cancellation_reason))->columnSpanFull(),
            ])->columns(2),
            Section::make('Timeline')->schema([
                RepeatableEntry::make('events')->label('')->schema([
                    TextEntry::make('event_type')->label('Peristiwa')->formatStateUsing(fn ($state) => $state->label()),
                    TextEntry::make('created_at')->label('Waktu dicatat')->dateTime('d M Y, H:i'),
                    TextEntry::make('old_scheduled_at')->label('Jadwal lama')->dateTime('d M Y, H:i')->placeholder('—'),
                    TextEntry::make('new_scheduled_at')->label('Jadwal baru')->dateTime('d M Y, H:i')->placeholder('—'),
                    TextEntry::make('note')->label('Catatan')->columnSpanFull(),
                ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_at')
            ->columns([
                TextColumn::make('booking_code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('vehicle.user.name')->label('Customer')->searchable(),
                TextColumn::make('vehicle.name')->label('Kendaraan')->description(fn (Booking $record) => $record->vehicle->plate_number)->searchable(),
                TextColumn::make('scheduled_at')->label('Jadwal')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (BookingStatus $state) => $state->label())->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(collect(BookingStatus::cases())->mapWithKeys(fn (BookingStatus $status) => [$status->value => $status->label()])),
                Filter::make('date')->schema([
                    DatePicker::make('scheduled_date')->label('Tanggal'),
                ])->query(fn (Builder $query, array $data) => $query->when($data['scheduled_date'] ?? null, fn (Builder $query, $date) => $query->whereDate('scheduled_at', $date))),
                Filter::make('customer')->schema([
                    Select::make('customer_id')->label('Customer')->searchable()->options(fn () => User::query()->where('role', UserRole::Customer)->orderBy('name')->pluck('name', 'id')),
                ])->query(fn (Builder $query, array $data) => $query->when($data['customer_id'] ?? null, fn (Builder $query, $id) => $query->whereHas('vehicle', fn (Builder $query) => $query->where('user_id', $id)))),
                Filter::make('vehicle')->schema([
                    Select::make('vehicle_id')->label('Kendaraan')->searchable()->options(fn () => Vehicle::query()->orderBy('name')->get()->mapWithKeys(fn (Vehicle $vehicle) => [$vehicle->id => $vehicle->name.' · '.$vehicle->plate_number])),
                ])->query(fn (Builder $query, array $data) => $query->when($data['vehicle_id'] ?? null, fn (Builder $query, $id) => $query->where('vehicle_id', $id))),
            ])
            ->recordActions([
                ViewAction::make(),
                self::confirmAction(),
                self::rescheduleAction(),
                self::cancelAction(),
            ]);
    }

    public static function confirmAction(): Action
    {
        return Action::make('confirm')->label('Konfirmasi')->icon(Heroicon::OutlinedCheckCircle)->color('success')
            ->visible(fn (Booking $record) => $record->status === BookingStatus::Pending)
            ->requiresConfirmation()
            ->schema([Textarea::make('note')->label('Catatan opsional')->maxLength(500)])
            ->action(fn (Booking $record, array $data) => self::runAction(fn () => app(ConfirmBookingAction::class)->execute(auth()->user(), $record, $data['note'] ?? null), 'Booking dikonfirmasi.'));
    }

    public static function rescheduleAction(): Action
    {
        return Action::make('reschedule')->label('Jadwalkan Ulang')->icon(Heroicon::OutlinedCalendarDateRange)
            ->visible(fn (Booking $record) => $record->status->canBeRescheduled())
            ->schema([
                DateTimePicker::make('scheduled_at')->label('Jadwal baru')->seconds(false)->minDate(now())->required(),
                Textarea::make('note')->label('Alasan / catatan')->maxLength(500),
            ])->action(fn (Booking $record, array $data) => self::runAction(fn () => app(RescheduleBookingAction::class)->execute(auth()->user(), $record, $data['scheduled_at'], $data['note'] ?? null), 'Jadwal booking diperbarui.'));
    }

    public static function cancelAction(): Action
    {
        return Action::make('cancel')->label('Batalkan')->icon(Heroicon::OutlinedXCircle)->color('danger')
            ->visible(fn (Booking $record) => $record->status->canBeCancelled())
            ->requiresConfirmation()
            ->schema([Textarea::make('reason')->label('Alasan pembatalan')->required()->minLength(5)->maxLength(500)])
            ->action(fn (Booking $record, array $data) => self::runAction(fn () => app(CancelBookingAction::class)->execute(auth()->user(), $record, $data['reason']), 'Booking dibatalkan.'));
    }

    private static function runAction(callable $callback, string $successMessage): void
    {
        try {
            $callback();
            Notification::make()->success()->title($successMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title('Perubahan tidak dapat disimpan')->body($exception->getMessage())->send();
        }
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'view' => ViewBooking::route('/{record}'),
        ];
    }
}
