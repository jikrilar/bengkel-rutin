<?php

namespace App\Console\Commands;

use App\Services\Notification\UpcomingBookingReminderService;
use Illuminate\Console\Command;

class SendUpcomingBookingReminders extends Command
{
    protected $signature = 'bookings:send-upcoming-reminders';

    protected $description = 'Send H-1 reminders for eligible confirmed bookings';

    public function handle(UpcomingBookingReminderService $service): int
    {
        $this->info($service->send().' upcoming booking reminders queued.');

        return self::SUCCESS;
    }
}
