<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\BookingEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingRescheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly BookingEvent $bookingEvent,
    ) {}
}
