<?php

use App\Exceptions\Fuzzy\InvalidFuzzyConfigurationException;
use App\Models\FuzzyConfig;
use App\Services\Fuzzy\FuzzyConfigResolver;
use Database\Seeders\FuzzyConfigSeeder;

it('resolves the single active versioned fuzzy configuration', function () {
    $this->seed(FuzzyConfigSeeder::class);

    $config = app(FuzzyConfigResolver::class)->resolve();

    expect($config->version)->toBe(1)
        ->and($config->is_active)->toBeTrue();
});

it('rejects missing or ambiguous active configurations', function () {
    $this->seed(FuzzyConfigSeeder::class);
    FuzzyConfig::query()->update(['is_active' => false]);

    expect(fn () => app(FuzzyConfigResolver::class)->resolve())
        ->toThrow(InvalidFuzzyConfigurationException::class);

    FuzzyConfig::query()->where('version', 1)->update(['is_active' => true]);
    FuzzyConfig::factory()->active()->create(['version' => 2]);

    expect(fn () => app(FuzzyConfigResolver::class)->resolve())
        ->toThrow(InvalidFuzzyConfigurationException::class);
});
