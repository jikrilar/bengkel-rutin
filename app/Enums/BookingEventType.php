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
}
