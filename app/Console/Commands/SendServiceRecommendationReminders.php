<?php

namespace App\Console\Commands;

use App\Services\Notification\ServiceRecommendationReminderService;
use Illuminate\Console\Command;

class SendServiceRecommendationReminders extends Command
{
    protected $signature = 'recommendations:send-reminders';

    protected $description = 'Send deduplicated service recommendation reminders';

    public function handle(ServiceRecommendationReminderService $service): int
    {
        $this->info($service->send().' service recommendation reminders queued.');

        return self::SUCCESS;
    }
}
