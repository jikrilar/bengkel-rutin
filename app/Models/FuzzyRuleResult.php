<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuzzyRuleResult extends Model
{
    protected $fillable = [
        'fuzzy_calculation_id',
        'fuzzy_rule_id',
        'alpha',
        'z_value',
        'weighted_value',
    ];

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(FuzzyCalculation::class, 'fuzzy_calculation_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FuzzyRule::class, 'fuzzy_rule_id');
    }

    protected function casts(): array
    {
        return [
            'alpha' => 'decimal:4',
            'z_value' => 'decimal:4',
            'weighted_value' => 'decimal:4',
        ];
    }
}
