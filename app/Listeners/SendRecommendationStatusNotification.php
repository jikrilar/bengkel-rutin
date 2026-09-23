<?php

namespace App\Listeners;

use App\Enums\RecommendationStatus;
use App\Events\RecommendationStatusChanged;
use App\Notifications\ServiceApproachingNotification;
use App\Notifications\ServiceUrgentNotification;
use App\Services\Notification\NotificationDeliveryService;
use App\Services\Notification\ServiceRecommendationReminderService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendRecommendationStatusNotification
{
    public function __construct(
        private readonly NotificationDeliveryService $delivery,
        private readonly ServiceRecommendationReminderService $reminders,
    ) {}

    public function handle(RecommendationStatusChanged $event): void
    {
        $calculation = $event->calculation->loadMissing('vehicle.user');
        $dedupKey = $this->reminders->cycleKey($calculation->vehicle, $calculation->final_status);
        $notification = match ($calculation->final_status) {
            RecommendationStatus::Approaching => new ServiceApproachingNotification($calculation, $dedupKey),
            RecommendationStatus::Urgent => new ServiceUrgentNotification($calculation, $dedupKey),
            default => null,
        };

        if ($notification !== null) {
            try {
                $this->delivery->sendOnce($calculation->vehicle->user, $notification, $dedupKey);
            } catch (Throwable $exception) {
                Log::error('Recommendation status notification could not be queued.', [
                    'fuzzy_calculation_id' => $calculation->id,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
