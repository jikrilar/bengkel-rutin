<?php

namespace App\Models;

use App\Enums\BaselineSource;
use App\Enums\BookingStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_profile_id',
        'name',
        'plate_number',
        'brand',
        'model',
        'year',
        'baseline_service_date',
        'baseline_odometer',
        'baseline_source',
        'baseline_service_record_id',
    ];

    public static function normalizePlateNumber(string $plateNumber): string
    {
        return preg_replace('/[^A-Z0-9]/', '', Str::upper($plateNumber)) ?? '';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function baselineServiceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class, 'baseline_service_record_id');
    }

    public function odometerLogs(): HasMany
    {
        return $this->hasMany(OdometerLog::class);
    }

    public function latestOdometer(): HasOne
    {
        return $this->hasOne(OdometerLog::class)->ofMany([
            'recorded_at' => 'max',
            'id' => 'max',
        ]);
    }

    public function fuzzyCalculations(): HasMany
    {
        return $this->hasMany(FuzzyCalculation::class);
    }

    public function latestFuzzyCalculation(): HasOne
    {
        return $this->hasOne(FuzzyCalculation::class)->ofMany([
            'calculated_at' => 'max',
            'id' => 'max',
        ]);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->ofMany(
            ['scheduled_at' => 'max', 'id' => 'max'],
            fn ($query) => $query->whereIn('status', BookingStatus::activeValues()),
        );
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    protected function plateNumber(): Attribute
    {
        return Attribute::make(
            set: function (string $value): array {
                $displayValue = Str::of($value)->upper()->squish()->toString();

                return [
                    'plate_number' => $displayValue,
                    'plate_number_normalized' => self::normalizePlateNumber($displayValue),
                ];
            },
        );
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'baseline_service_date' => 'date',
            'baseline_odometer' => 'integer',
            'baseline_source' => BaselineSource::class,
        ];
    }
}
