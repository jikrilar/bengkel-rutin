<?php

namespace App\Models;

use Database\Factories\FuzzyConfigFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuzzyConfig extends Model
{
    /** @use HasFactory<FuzzyConfigFactory> */
    use HasFactory;

    protected $fillable = [
        'version',
        'progress_safe_end',
        'progress_approaching_peak',
        'progress_critical_full',
        'usage_normal_full_until',
        'usage_intensive_full_from',
        'is_active',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(FuzzyCalculation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'progress_safe_end' => 'decimal:2',
            'progress_approaching_peak' => 'decimal:2',
            'progress_critical_full' => 'decimal:2',
            'usage_normal_full_until' => 'decimal:2',
            'usage_intensive_full_from' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
