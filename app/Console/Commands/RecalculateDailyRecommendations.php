<?php

namespace App\Console\Commands;

use App\Services\Recommendation\DailyRecommendationService;
use Illuminate\Console\Command;

class RecalculateDailyRecommendations extends Command
{
    protected $signature = 'recommendations:recalculate-daily';

    protected $description = 'Recalculate eligible vehicle recommendations once per day';

    public function handle(DailyRecommendationService $service): int
    {
        $result = $service->run();
        $this->info(sprintf(
            'Daily recommendations: %d processed, %d skipped, %d failed.',
            $result['processed'],
            $result['skipped'],
            $result['failed'],
        ));

        return $result['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
