<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingEvent;
use Illuminate\Notifications\Messages\MailMessage;

class BookingRescheduledNotification extends WorkshopNotification
{
    public function __construct(
        public readonly Booking $booking,
        public readonly BookingEvent $bookingEvent,
        public readonly string $dedupKey,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Jadwal booking servis diperbarui')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message())
            ->action('Lihat booking', route('bookings.show', $this->booking));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_rescheduled',
            'title' => 'Jadwal booking diperbarui',
            'message' => $this->message(),
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'booking_event_id' => $this->bookingEvent->id,
            'target_url' => route('bookings.show', $this->booking, false),
            'dedup_key' => $this->dedupKey,
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Booking %s dijadwalkan ulang menjadi %s pukul %s.',
            $this->booking->booking_code,
            $this->booking->scheduled_at->translatedFormat('d F Y'),
            $this->booking->scheduled_at->format('H.i'),
        );
    }
}
