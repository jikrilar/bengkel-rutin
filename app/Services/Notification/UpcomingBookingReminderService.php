<?php

namespace App\Services\Notification;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\UpcomingBookingNotification;
use Carbon\CarbonImmutable;

class UpcomingBookingReminderService
{
    public function __construct(private readonly NotificationDeliveryService $delivery) {}

    public function send(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now(config('app.timezone'));
        $tomorrow = $now->addDay();
        $sent = 0;

        Booking::query()
            ->with('vehicle.user')
            ->where('status', BookingStatus::Confirmed)
            ->whereBetween('scheduled_at', [$tomorrow->startOfDay(), $tomorrow->endOfDay()])
            ->orderBy('scheduled_at')
            ->get()
            ->each(function (Booking $booking) use (&$sent): void {
                $dedupKey = 'booking-upcoming:'.$booking->id.':'.$booking->scheduled_at->format('YmdHi');

                if ($this->delivery->sendOnce(
                    $booking->vehicle->user,
                    new UpcomingBookingNotification($booking, $dedupKey),
                    $dedupKey,
                )) {
                    $sent++;
                }
            });

        return $sent;
    }
}
