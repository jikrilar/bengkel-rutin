<?php

namespace App\Services\Fuzzy;

use App\Exceptions\Fuzzy\InvalidFuzzyConfigurationException;
use App\Models\FuzzyConfig;

class FuzzyConfigResolver
{
    public function resolve(): FuzzyConfig
    {
        $activeConfigs = FuzzyConfig::query()
            ->active()
            ->orderByDesc('version')
            ->limit(2)
            ->get();

        if ($activeConfigs->count() !== 1) {
            throw new InvalidFuzzyConfigurationException(
                'Exactly one active fuzzy configuration is required.',
            );
        }

        return $activeConfigs->firstOrFail();
    }
}
