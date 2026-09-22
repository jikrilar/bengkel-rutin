<?php

namespace App\Models;

use Database\Factories\ServiceProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProfile extends Model
{
    /** @use HasFactory<ServiceProfileFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'interval_km',
        'interval_days',
        'description',
        'is_active',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    protected function casts(): array
    {
        return [
            'interval_km' => 'integer',
            'interval_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
