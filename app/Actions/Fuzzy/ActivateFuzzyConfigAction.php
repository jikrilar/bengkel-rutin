<?php

namespace App\Actions\Fuzzy;

use App\Enums\UserRole;
use App\Jobs\RecalculateVehiclesForFuzzyConfig;
use App\Models\FuzzyConfig;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActivateFuzzyConfigAction
{
    public const DEFAULTS = [
        'progress_safe_end' => 70,
        'progress_approaching_peak' => 90,
        'progress_critical_full' => 100,
        'usage_normal_full_until' => 80,
        'usage_intensive_full_from' => 120,
    ];

    /** @param array<string, int|float|string> $data */
    public function execute(User $admin, array $data): FuzzyConfig
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat mengubah konfigurasi fuzzy.');
        }

        $validated = Validator::make($data, [
            'progress_safe_end' => ['required', 'numeric', 'min:0', 'lt:progress_approaching_peak'],
            'progress_approaching_peak' => ['required', 'numeric', 'gt:progress_safe_end', 'lt:progress_critical_full'],
            'progress_critical_full' => ['required', 'numeric', 'gt:progress_approaching_peak'],
            'usage_normal_full_until' => ['required', 'numeric', 'min:0', 'lt:usage_intensive_full_from'],
            'usage_intensive_full_from' => ['required', 'numeric', 'gt:usage_normal_full_until'],
        ])->validate();

        $config = DB::transaction(function () use ($admin, $validated): FuzzyConfig {
            $configs = FuzzyConfig::query()->lockForUpdate()->get();
            $nextVersion = ((int) $configs->max('version')) + 1;

            FuzzyConfig::query()->where('is_active', true)->update(['is_active' => false]);

            return FuzzyConfig::query()->create([
                ...$validated,
                'version' => $nextVersion,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        }, attempts: 3);

        RecalculateVehiclesForFuzzyConfig::dispatch($config->id);

        return $config;
    }

    public function reset(User $admin): FuzzyConfig
    {
        return $this->execute($admin, self::DEFAULTS);
    }
}
