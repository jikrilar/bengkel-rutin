<?php

namespace App\Exceptions\Booking;

use DomainException;

class InvalidBookingTransitionException extends DomainException
{
    // Explicit domain exception for invalid booking lifecycle changes.
}
