<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use App\Models\WorkshopSetting;
use Illuminate\Database\Seeder;

class OperatingHourSeeder extends Seeder
{
    public function run(): void
    {
        $workshop = WorkshopSetting::query()->firstOrFail();
        $now = now();

        $hours = collect(range(1, 7))->map(fn (int $day): array => [
            'workshop_setting_id' => $workshop->getKey(),
            'day_of_week' => $day,
            'is_open' => $day !== 7,
            'open_time' => $day !== 7 ? '08:00:00' : null,
            'close_time' => $day !== 7 ? '17:00:00' : null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        OperatingHour::query()->upsert(
            $hours,
            ['workshop_setting_id', 'day_of_week'],
            ['is_open', 'open_time', 'close_time', 'updated_at'],
        );
    }
}
