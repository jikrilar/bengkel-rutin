<?php

namespace Database\Seeders;

use App\Models\WorkshopSetting;
use Illuminate\Database\Seeder;

class WorkshopSettingSeeder extends Seeder
{
    public function run(): void
    {
        WorkshopSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'workshop_name' => config('workshop.name'),
                'address' => config('workshop.address'),
                'phone' => config('workshop.phone'),
                'email' => config('workshop.email'),
                'timezone' => config('workshop.timezone'),
                'slot_duration_minutes' => config('workshop.slot_duration_minutes'),
                'slot_capacity' => config('workshop.slot_capacity'),
            ],
        );
    }
}
