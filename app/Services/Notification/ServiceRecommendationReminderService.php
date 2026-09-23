<?php

namespace App\Services\Notification;

use App\Enums\RecommendationStatus;
use App\Models\Vehicle;
use App\Notifications\ServiceApproachingNotification;
use App\Notifications\ServiceUrgentNotification;

class ServiceRecommendationReminderService
{
    public function __construct(private readonly NotificationDeliveryService $delivery) {}

    public function send(): int
    {
        $sent = 0;

        Vehicle::query()
            ->with(['user', 'latestFuzzyCalculation.vehicle'])
            ->whereHas('latestFuzzyCalculation', fn ($query) => $query->whereIn('final_status', [
                RecommendationStatus::Approaching,
                RecommendationStatus::Urgent,
            ]))
            ->whereDoesntHave('activeBooking')
            ->orderBy('id')
            ->get()
            ->each(function (Vehicle $vehicle) use (&$sent): void {
                $calculation = $vehicle->latestFuzzyCalculation;
                $status = $calculation->final_status;
                $dedupKey = $this->cycleKey($vehicle, $status);
                $notification = $status === RecommendationStatus::Urgent
                    ? new ServiceUrgentNotification($calculation, $dedupKey, true)
                    : new ServiceApproachingNotification($calculation, $dedupKey, true);

                if ($this->delivery->sendOnce($vehicle->user, $notification, $dedupKey)) {
                    $sent++;
                }
            });

        return $sent;
    }

    public function cycleKey(Vehicle $vehicle, RecommendationStatus $status): string
    {
        return implode(':', [
            'service-status',
            $vehicle->id,
            $vehicle->baseline_service_date?->format('Y-m-d') ?? 'none',
            $vehicle->baseline_odometer ?? 'none',
            $status->value,
        ]);
    }
}
