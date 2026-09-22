<?php

namespace App\Models;

use App\Enums\CalculationTrigger;
use App\Enums\RecommendationStatus;
use Database\Factories\FuzzyCalculationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuzzyCalculation extends Model
{
    /** @use HasFactory<FuzzyCalculationFactory> */
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'fuzzy_config_id',
        'trigger_type',
        'interval_km_snapshot',
        'interval_days_snapshot',
        'current_odometer',
        'baseline_odometer',
        'baseline_service_date',
        'km_since_service',
        'days_since_service',
        'average_daily_km',
        'baseline_daily_usage',
        'progress_km',
        'progress_time',
        'usage_intensity',
        'km_safe_mu',
        'km_approaching_mu',
        'km_critical_mu',
        'time_safe_mu',
        'time_approaching_mu',
        'time_critical_mu',
        'usage_normal_mu',
        'usage_intensive_mu',
        'score',
        'fuzzy_status',
        'estimated_due_by_km',
        'estimated_due_by_time',
        'estimated_due_date',
        'recommended_from_date',
        'recommended_to_date',
        'recommended_date',
        'final_status',
        'guard_applied',
        'guard_reason',
        'calculated_at',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fuzzyConfig(): BelongsTo
    {
        return $this->belongsTo(FuzzyConfig::class);
    }

    public function ruleResults(): HasMany
    {
        return $this->hasMany(FuzzyRuleResult::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'recommendation_calculation_id');
    }

    protected function casts(): array
    {
        return [
            'trigger_type' => CalculationTrigger::class,
            'interval_km_snapshot' => 'integer',
            'interval_days_snapshot' => 'integer',
            'current_odometer' => 'integer',
            'baseline_odometer' => 'integer',
            'baseline_service_date' => 'date',
            'km_since_service' => 'integer',
            'days_since_service' => 'integer',
            'average_daily_km' => 'decimal:2',
            'baseline_daily_usage' => 'decimal:2',
            'progress_km' => 'decimal:2',
            'progress_time' => 'decimal:2',
            'usage_intensity' => 'decimal:2',
            'km_safe_mu' => 'decimal:4',
            'km_approaching_mu' => 'decimal:4',
            'km_critical_mu' => 'decimal:4',
            'time_safe_mu' => 'decimal:4',
            'time_approaching_mu' => 'decimal:4',
            'time_critical_mu' => 'decimal:4',
            'usage_normal_mu' => 'decimal:4',
            'usage_intensive_mu' => 'decimal:4',
            'score' => 'decimal:2',
            'fuzzy_status' => RecommendationStatus::class,
            'estimated_due_by_km' => 'date',
            'estimated_due_by_time' => 'date',
            'estimated_due_date' => 'date',
            'recommended_from_date' => 'date',
            'recommended_to_date' => 'date',
            'recommended_date' => 'date',
            'final_status' => RecommendationStatus::class,
            'guard_applied' => 'boolean',
            'calculated_at' => 'datetime',
        ];
    }
}
