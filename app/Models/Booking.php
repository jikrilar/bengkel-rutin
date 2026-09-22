<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'vehicle_id',
        'recommendation_calculation_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'complaint',
        'cancellation_reason',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recommendationCalculation(): BelongsTo
    {
        return $this->belongsTo(FuzzyCalculation::class, 'recommendation_calculation_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class);
    }

    public function serviceRecord(): HasOne
    {
        return $this->hasOne(ServiceRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', BookingStatus::activeValues());
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BookingStatus::Completed->value,
            BookingStatus::Cancelled->value,
        ]);
    }

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'status' => BookingStatus::class,
        ];
    }
}
