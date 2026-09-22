<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleException extends Model
{
    protected $fillable = [
        'workshop_setting_id',
        'date',
        'is_closed',
        'open_time',
        'close_time',
        'reason',
    ];

    public function workshopSetting(): BelongsTo
    {
        return $this->belongsTo(WorkshopSetting::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_closed' => 'boolean',
        ];
    }
}
