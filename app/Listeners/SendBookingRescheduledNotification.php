<?php

namespace App\Listeners;

use App\Events\BookingRescheduled;
use App\Notifications\BookingRescheduledNotification;
use App\Services\Notification\NotificationDeliveryService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBookingRescheduledNotification
{
    public function __construct(private readonly NotificationDeliveryService $delivery) {}

    public function handle(BookingRescheduled $event): void
    {
        $booking = $event->booking->loadMissing('vehicle.user');
        $dedupKey = 'booking-rescheduled:'.$event->bookingEvent->id;

        try {
            $this->delivery->sendOnce(
                $booking->vehicle->user,
                new BookingRescheduledNotification($booking, $event->bookingEvent, $dedupKey),
                $dedupKey,
            );
        } catch (Throwable $exception) {
            Log::error('Booking reschedule notification could not be queued.', [
                'booking_id' => $booking->id,
                'exception' => $exception,
            ]);
        }
    }
}
