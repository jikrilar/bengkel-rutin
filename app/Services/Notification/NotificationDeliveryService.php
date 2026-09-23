<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class NotificationDeliveryService
{
    public function sendOnce(User $user, Notification $notification, string $dedupKey): bool
    {
        $alreadyDelivered = $user->notifications()
            ->where('data->dedup_key', $dedupKey)
            ->exists();

        if ($alreadyDelivered) {
            return false;
        }

        $lockKey = 'notification-dedup:'.sha1($user->getKey().'|'.$dedupKey);

        if (! Cache::add($lockKey, true, now()->addHour())) {
            return false;
        }

        $user->notify($notification->afterCommit());

        return true;
    }
}
