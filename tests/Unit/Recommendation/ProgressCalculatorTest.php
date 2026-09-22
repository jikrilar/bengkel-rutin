<?php

use App\Services\Recommendation\ProgressCalculator;

it('calculates kilometer and time progress from the service baseline', function () {
    $result = (new ProgressCalculator)->calculate(
        currentOdometer: 13000,
        baselineOdometer: 10000,
        baselineServiceDate: new DateTimeImmutable('2026-01-01'),
        calculatedAt: new DateTimeImmutable('2026-04-01'),
        intervalKm: 4000,
        intervalDays: 120,
    );

    expect($result['km_since_service'])->toBe(3000)
        ->and($result['days_since_service'])->toBe(90)
        ->and($result['progress_km'])->toBe(75.0)
        ->and($result['progress_time'])->toBe(75.0);
});

it('rejects an odometer below the baseline', function () {
    (new ProgressCalculator)->calculate(
        9999,
        10000,
        new DateTimeImmutable('2026-01-01'),
        new DateTimeImmutable('2026-04-01'),
        4000,
        120,
    );
})->throws(DomainException::class);
