<?php

namespace App\Notifications;

use App\Models\FuzzyCalculation;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceUrgentNotification extends WorkshopNotification
{
    public function __construct(
        public readonly FuzzyCalculation $calculation,
        public readonly string $dedupKey,
        public readonly bool $reminder = false,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kendaraan perlu segera diservis')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message())
            ->action('Lihat rekomendasi', route('recommendations.show', $this->calculation->vehicle));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $vehicle = $this->calculation->vehicle;

        return [
            'type' => 'service_urgent',
            'title' => $this->reminder ? 'Pengingat servis mendesak' : 'Kendaraan perlu segera diservis',
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
            '%s (%s) berstatus Segera Servis. Pilih jadwal bengkel sesegera mungkin.',
            $this->calculation->vehicle->name,
            $this->calculation->vehicle->plate_number,
        );
    }
}
