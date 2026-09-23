<?php

namespace App\Notifications;

use App\Models\FuzzyCalculation;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceApproachingNotification extends WorkshopNotification
{
    public function __construct(
        public readonly FuzzyCalculation $calculation,
        public readonly string $dedupKey,
        public readonly bool $reminder = false,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->calculation->vehicle;

        return (new MailMessage)
            ->subject($this->reminder ? 'Pengingat jadwal servis kendaraan' : 'Servis kendaraan mulai mendekat')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message())
            ->line('Tanggal rekomendasi: '.$this->calculation->recommended_date->translatedFormat('d F Y').'.')
            ->action('Lihat rekomendasi', route('recommendations.show', $vehicle));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $vehicle = $this->calculation->vehicle;

        return [
            'type' => 'service_approaching',
            'title' => $this->reminder ? 'Pengingat servis kendaraan' : 'Servis kendaraan mulai mendekat',
            'message' => $this->message(),
            'vehicle_id' => $vehicle->id,
            'fuzzy_calculation_id' => $this->calculation->id,
            'target_url' => route('recommendations.show', $vehicle, false),
            'dedup_key' => $this->dedupKey,
        ];
    }

    private function message(): string
    {
        return sprintf(
            '%s (%s) disarankan melakukan servis pada %s.',
            $this->calculation->vehicle->name,
            $this->calculation->vehicle->plate_number,
            $this->calculation->recommended_date->translatedFormat('d F Y'),
        );
    }
}
