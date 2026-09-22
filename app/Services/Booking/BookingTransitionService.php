<?php

namespace App\Services\Booking;

use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Exceptions\Booking\InvalidBookingTransitionException;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;

class BookingTransitionService
{
    public function transition(
        Booking $booking,
        BookingStatus $newStatus,
        User $actor,
        ?string $note = null,
    ): Booking {
        $oldStatus = $booking->status;

        if (! $oldStatus->canTransitionTo($newStatus)) {
            throw new InvalidBookingTransitionException(sprintf(
                'Transisi booking dari %s ke %s tidak diizinkan.',
                $oldStatus->label(),
                $newStatus->label(),
            ));
        }

        $booking->update([
            'status' => $newStatus,
            'cancellation_reason' => $newStatus === BookingStatus::Cancelled ? $note : null,
        ]);
        $booking->events()->create([
            'event_type' => match ($newStatus) {
                BookingStatus::Confirmed => BookingEventType::Confirmed,
                BookingStatus::InService => BookingEventType::Started,
                BookingStatus::Completed => BookingEventType::Completed,
                BookingStatus::Cancelled => BookingEventType::Cancelled,
                default => throw new InvalidBookingTransitionException('Event untuk transisi booking tidak tersedia.'),
            },
            'actor_user_id' => $actor->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
        ]);

        return $booking->refresh();
    }

    public function reschedule(
        Booking $booking,
        DateTimeInterface $newSchedule,
        User $actor,
        ?string $note = null,
    ): Booking {
        if (! $booking->status->canBeRescheduled()) {
            throw new InvalidBookingTransitionException(sprintf(
                'Booking berstatus %s tidak dapat dijadwalkan ulang.',
                $booking->status->label(),
            ));
        }

        $oldSchedule = CarbonImmutable::instance($booking->scheduled_at);
        $booking->update(['scheduled_at' => $newSchedule]);
        $booking->events()->create([
            'event_type' => BookingEventType::Rescheduled,
            'actor_user_id' => $actor->id,
            'old_status' => $booking->status,
            'new_status' => $booking->status,
            'old_scheduled_at' => $oldSchedule,
            'new_scheduled_at' => $newSchedule,
            'note' => $note,
        ]);

        return $booking->refresh();
    }
}
