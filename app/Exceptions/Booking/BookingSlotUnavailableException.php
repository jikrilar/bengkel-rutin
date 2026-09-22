<?php

namespace App\Exceptions\Booking;

use DomainException;

class BookingSlotUnavailableException extends DomainException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
