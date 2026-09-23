<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Notifications\BookingConfirmedNotification;
use App\Services\Notification\NotificationDeliveryService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBookingConfirmedNotification
{
    public function __construct(private readonly NotificationDeliveryService $delivery) {}

    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking->loadMissing('vehicle.user');
        $dedupKey = 'booking-confirmed:'.$booking->id;

        try {
            $this->delivery->sendOnce(
                $booking->vehicle->user,
                new BookingConfirmedNotification($booking, $dedupKey),
                $dedupKey,
            );
        } catch (Throwable $exception) {
            Log::error('Booking confirmation notification could not be queued.', [
                'booking_id' => $booking->id,
                'exception' => $exception,
            ]);
        }
    }
}
