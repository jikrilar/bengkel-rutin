<?php

use App\Services\Recommendation\UsageCalculator;

it('calculates actual daily usage from ordered odometer history', function () {
    $result = (new UsageCalculator)->calculate([
        ['odometer' => 11000, 'recorded_at' => new DateTimeImmutable('2026-01-11')],
        ['odometer' => 10000, 'recorded_at' => new DateTimeImmutable('2026-01-01')],
    ], 4000, 100);

    expect($result['average_daily_km'])->toBe(100.0)
        ->and($result['baseline_daily_usage'])->toBe(40.0)
        ->and($result['usage_intensity'])->toBe(250.0)
        ->and($result['fallback_applied'])->toBeFalse();
});

it('uses the locked neutral fallback when history is insufficient', function () {
    $result = (new UsageCalculator)->calculate([
        ['odometer' => 10000, 'recorded_at' => new DateTimeImmutable('2026-01-01')],
    ], 4000, 120);

    expect($result['average_daily_km'])->toEqualWithDelta(33.333333, 0.000001)
        ->and($result['baseline_daily_usage'])->toEqualWithDelta(33.333333, 0.000001)
        ->and($result['usage_intensity'])->toBe(100.0)
        ->and($result['fallback_applied'])->toBeTrue();
});

it('also falls back for readings without a positive day span', function () {
    $result = (new UsageCalculator)->calculate([
        ['odometer' => 10000, 'recorded_at' => new DateTimeImmutable('2026-01-01 08:00')],
        ['odometer' => 10100, 'recorded_at' => new DateTimeImmutable('2026-01-01 18:00')],
    ], 4000, 120);

    expect($result['usage_intensity'])->toBe(100.0)
        ->and($result['fallback_applied'])->toBeTrue();
});
