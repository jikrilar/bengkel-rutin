<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuzzyRule extends Model
{
    protected $fillable = [
        'code',
        'km_state',
        'time_state',
        'usage_state',
        'consequent',
        'description',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(FuzzyRuleResult::class);
    }
}
