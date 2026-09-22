<?php

namespace App\Filament\Resources\Vehicles;

use App\Actions\Service\AssignServiceProfileAction;
use App\Actions\Vehicle\CorrectOdometerAction;
use App\Enums\OdometerSource;
use App\Enums\RecommendationStatus;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Filament\Resources\Vehicles\Pages\ViewVehicle;
use App\Models\ServiceProfile;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;
use UnitEnum;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $modelLabel = 'kendaraan';

    protected static ?string $recordTitleAttribute = 'name';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kendaraan dan pemilik')->schema([
                TextEntry::make('name')->label('Nama kendaraan'),
                TextEntry::make('plate_number')->label('Nomor polisi'),
                TextEntry::make('brand')->label('Merek / model')->formatStateUsing(fn ($state, Vehicle $record) => $state.' '.$record->model),
                TextEntry::make('year')->label('Tahun'),
                TextEntry::make('user.name')->label('Customer'),
                TextEntry::make('user.email')->label('Email customer'),
            ])->columns(2),
            Section::make('Siklus servis aktif')->schema([
                TextEntry::make('serviceProfile.name')->label('Profil servis'),
                TextEntry::make('serviceProfile.interval_km')->label('Interval kilometer')->numeric()->suffix(' km'),
                TextEntry::make('serviceProfile.interval_days')->label('Interval waktu')->suffix(' hari'),
                TextEntry::make('latestOdometer.odometer')->label('Odometer terbaru')->numeric()->suffix(' km'),
                TextEntry::make('baseline_service_date')->label('Baseline tanggal')->date('d M Y')->placeholder('Belum tersedia'),
                TextEntry::make('baseline_odometer')->label('Baseline odometer')->numeric()->suffix(' km')->placeholder('Belum tersedia'),
            ])->columns(2),
            Section::make('Rekomendasi terbaru')->schema([
                TextEntry::make('latestFuzzyCalculation.final_status')->label('Status')->formatStateUsing(fn (?RecommendationStatus $state) => $state?->label() ?? 'Rekomendasi Belum Tersedia')->badge(),
                TextEntry::make('latestFuzzyCalculation.score')->label('Skor fuzzy')->numeric(decimalPlaces: 2)->suffix(' / 100')->placeholder('—'),
                TextEntry::make('latestFuzzyCalculation.progress_km')->label('Progress kilometer')->numeric(decimalPlaces: 2)->suffix('%')->placeholder('—'),
                TextEntry::make('latestFuzzyCalculation.progress_time')->label('Progress waktu')->numeric(decimalPlaces: 2)->suffix('%')->placeholder('—'),
                TextEntry::make('latestFuzzyCalculation.recommended_date')->label('Tanggal rekomendasi')->date('d M Y')->placeholder('—'),
                TextEntry::make('latestFuzzyCalculation.calculated_at')->label('Dihitung')->dateTime('d M Y, H:i')->placeholder('—'),
            ])->columns(2),
            Section::make('Histori odometer')->schema([
                RepeatableEntry::make('odometerLogs')->label('')->schema([
                    TextEntry::make('recorded_at')->label('Dicatat')->dateTime('d M Y, H:i'),
                    TextEntry::make('odometer')->label('Odometer')->numeric()->suffix(' km'),
                    TextEntry::make('source')->label('Sumber')->formatStateUsing(fn (OdometerSource $state) => $state->label()),
                    TextEntry::make('correction_reason')->label('Alasan koreksi')->placeholder('—'),
                ])->columns(2),
            ])->collapsible()->collapsed(),
            Section::make('Riwayat booking')->schema([
                RepeatableEntry::make('bookings')->label('')->schema([
                    TextEntry::make('booking_code')->label('Kode'),
                    TextEntry::make('scheduled_at')->label('Jadwal')->dateTime('d M Y, H:i'),
                    TextEntry::make('status')->label('Status')->formatStateUsing(fn ($state) => $state->label())->badge(),
                ])->columns(3),
            ])->collapsible()->collapsed(),
            Section::make('Riwayat servis')->schema([
                RepeatableEntry::make('serviceRecords')->label('')->schema([
                    TextEntry::make('service_code')->label('Kode'),
                    TextEntry::make('service_date')->label('Tanggal')->dateTime('d M Y'),
                    TextEntry::make('service_type')->label('Jenis servis'),
                    TextEntry::make('total_cost')->label('Biaya')->money('IDR'),
                ])->columns(2),
            ])->collapsible()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Kendaraan')->description(fn (Vehicle $record) => $record->brand.' '.$record->model)->searchable()->sortable(),
            TextColumn::make('plate_number')->label('Nomor polisi')->searchable(['plate_number', 'plate_number_normalized']),
            TextColumn::make('user.name')->label('Customer')->searchable(),
            TextColumn::make('serviceProfile.name')->label('Profil servis'),
            TextColumn::make('latestOdometer.odometer')->label('Odometer')->numeric()->suffix(' km'),
            TextColumn::make('latestFuzzyCalculation.final_status')->label('Rekomendasi')->formatStateUsing(fn (?RecommendationStatus $state) => $state?->label() ?? 'Belum tersedia')->badge(),
        ])->filters([
            SelectFilter::make('recommendation_status')->label('Status rekomendasi')->options(collect(RecommendationStatus::cases())->mapWithKeys(fn (RecommendationStatus $status) => [$status->value => $status->label()]))
                ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $query, string $status) => $query->whereHas('latestFuzzyCalculation', fn (Builder $query) => $query->where('final_status', $status)))),
        ])->recordActions([
            ViewAction::make(),
            self::correctOdometerAction(),
            self::assignProfileAction(),
        ]);
    }

    public static function correctOdometerAction(): Action
    {
        return Action::make('correctOdometer')->label('Koreksi Odometer')->icon(Heroicon::OutlinedPencilSquare)
            ->modalDescription(fn (Vehicle $record) => 'Nilai terbaru: '.number_format($record->latestOdometer?->odometer ?? 0, 0, ',', '.').' km. Koreksi disimpan sebagai histori baru dan tidak menghapus catatan lama.')
            ->schema([
                TextInput::make('current_value')->label('Nilai saat ini')->default(fn (Vehicle $record) => $record->latestOdometer?->odometer)->disabled()->dehydrated(false)->suffix('km'),
                TextInput::make('corrected_value')->label('Nilai terkoreksi')->numeric()->minValue(fn (Vehicle $record) => $record->latestOdometer?->odometer ?? 0)->required()->suffix('km'),
                Textarea::make('reason')->label('Alasan koreksi')->required()->minLength(5)->maxLength(1000),
            ])->action(fn (Vehicle $record, array $data) => self::runAction(fn () => app(CorrectOdometerAction::class)->execute(auth()->user(), $record, (int) $data['corrected_value'], $data['reason']), 'Koreksi odometer tersimpan dan rekomendasi diperbarui.'));
    }

    public static function assignProfileAction(): Action
    {
        return Action::make('assignProfile')->label('Ganti Profil Servis')->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->schema([
                Select::make('service_profile_id')->label('Profil servis')->options(fn () => ServiceProfile::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
            ])->action(fn (Vehicle $record, array $data) => self::runAction(fn () => app(AssignServiceProfileAction::class)->execute(auth()->user(), $record, ServiceProfile::query()->findOrFail($data['service_profile_id'])), 'Profil servis diganti dan rekomendasi diperbarui.'));
    }

    private static function runAction(callable $callback, string $success): void
    {
        try {
            $callback();
            Notification::make()->success()->title($success)->send();
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
            'index' => ListVehicles::route('/'),
            'view' => ViewVehicle::route('/{record}'),
        ];
    }
}
