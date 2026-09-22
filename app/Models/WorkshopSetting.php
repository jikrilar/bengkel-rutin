<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'workshop_name',
        'address',
        'phone',
        'email',
        'timezone',
        'slot_duration_minutes',
        'slot_capacity',
    ];

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class);
    }

    public function scheduleExceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class);
    }

    protected function casts(): array
    {
        return [
            'slot_duration_minutes' => 'integer',
            'slot_capacity' => 'integer',
        ];
    }
}
