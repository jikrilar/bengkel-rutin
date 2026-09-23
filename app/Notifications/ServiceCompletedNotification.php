<?php

namespace App\Notifications;

use App\Models\ServiceRecord;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceCompletedNotification extends WorkshopNotification
{
    public function __construct(
        public readonly ServiceRecord $serviceRecord,
        public readonly string $dedupKey,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Servis kendaraan selesai')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message())
            ->action('Lihat riwayat servis', route('service-history.show', $this->serviceRecord));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_completed',
            'title' => 'Servis kendaraan selesai',
            'message' => $this->message(),
            'service_record_id' => $this->serviceRecord->id,
            'booking_id' => $this->serviceRecord->booking_id,
            'vehicle_id' => $this->serviceRecord->vehicle_id,
            'target_url' => route('service-history.show', $this->serviceRecord, false),
            'dedup_key' => $this->dedupKey,
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Servis %s telah selesai pada %s. Baseline perawatan kendaraan sudah diperbarui.',
            $this->serviceRecord->vehicle->name,
            $this->serviceRecord->service_date->translatedFormat('d F Y'),
        );
    }
}
