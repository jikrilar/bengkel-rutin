<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatingHour extends Model
{
    protected $fillable = [
        'workshop_setting_id',
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
    ];

    public function workshopSetting(): BelongsTo
    {
        return $this->belongsTo(WorkshopSetting::class);
    }

    public static function dayLabel(int $day): string
    {
        return match ($day) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => 'Tidak diketahui',
        };
    }

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
        ];
    }
}
