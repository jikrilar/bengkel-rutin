<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InService = 'in_service';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public static function active(): array
    {
        return [self::Pending, self::Confirmed, self::InService];
    }

    /** @return list<string> */
    public static function activeValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::active());
    }
}
