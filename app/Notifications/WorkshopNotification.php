<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

abstract class WorkshopNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }
}
