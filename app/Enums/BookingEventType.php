<?php

namespace App\Enums;

enum BookingEventType: string
{
    case Created = 'created';
    case Confirmed = 'confirmed';
    case Rescheduled = 'rescheduled';
    case Started = 'started';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Booking dibuat',
            self::Confirmed => 'Booking dikonfirmasi',
            self::Rescheduled => 'Jadwal diubah',
            self::Started => 'Servis dimulai',
            self::Completed => 'Servis selesai',
            self::Cancelled => 'Booking dibatalkan',
        };
    }
}
