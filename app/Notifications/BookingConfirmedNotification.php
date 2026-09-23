<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmedNotification extends WorkshopNotification
{
    public function __construct(
        public readonly Booking $booking,
        public readonly string $dedupKey,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking servis dikonfirmasi')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message())
            ->action('Lihat booking', route('bookings.show', $this->booking));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_confirmed',
            'title' => 'Booking dikonfirmasi',
            'message' => $this->message(),
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'target_url' => route('bookings.show', $this->booking, false),
            'dedup_key' => $this->dedupKey,
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Jadwal servis %s telah dikonfirmasi untuk %s pukul %s.',
            $this->booking->vehicle->name,
            $this->booking->scheduled_at->translatedFormat('d F Y'),
            $this->booking->scheduled_at->format('H.i'),
        );
    }
}
