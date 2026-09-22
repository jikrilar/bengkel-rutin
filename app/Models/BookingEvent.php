<?php

namespace App\Models;

use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingEvent extends Model
{
    protected $fillable = [
        'booking_id',
        'event_type',
        'actor_user_id',
        'old_status',
        'new_status',
        'old_scheduled_at',
        'new_scheduled_at',
        'note',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    protected function casts(): array
    {
        return [
            'event_type' => BookingEventType::class,
            'old_status' => BookingStatus::class,
            'new_status' => BookingStatus::class,
            'old_scheduled_at' => 'datetime',
            'new_scheduled_at' => 'datetime',
        ];
    }
}
