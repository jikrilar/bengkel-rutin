<?php

namespace App\Listeners;

use App\Events\ServiceCompleted;
use App\Notifications\ServiceCompletedNotification;
use App\Services\Notification\NotificationDeliveryService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendServiceCompletedNotification
{
    public function __construct(private readonly NotificationDeliveryService $delivery) {}

    public function handle(ServiceCompleted $event): void
    {
        $record = $event->serviceRecord->loadMissing('vehicle.user');
        $dedupKey = 'service-completed:'.$record->id;

        try {
            $this->delivery->sendOnce(
                $record->vehicle->user,
                new ServiceCompletedNotification($record, $dedupKey),
                $dedupKey,
            );
        } catch (Throwable $exception) {
            Log::error('Service completion notification could not be queued.', [
                'service_record_id' => $record->id,
                'exception' => $exception,
            ]);
        }
    }
}
