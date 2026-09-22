<?php

namespace Database\Seeders;

use App\Models\FuzzyConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuzzyConfigSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $default = FuzzyConfig::query()->firstOrCreate(
                ['version' => 1],
                [
                    'progress_safe_end' => 70,
                    'progress_approaching_peak' => 90,
                    'progress_critical_full' => 100,
                    'usage_normal_full_until' => 80,
                    'usage_intensive_full_from' => 120,
                    'is_active' => true,
                    'created_by' => null,
                ],
            );

            $active = FuzzyConfig::query()
                ->where('is_active', true)
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            $active ??= $default;

            FuzzyConfig::query()
                ->where('is_active', true)
                ->where('id', '!=', $active->getKey())
                ->update(['is_active' => false]);

            if (! $active->is_active) {
                $active->update(['is_active' => true]);
            }
        });
    }
}
