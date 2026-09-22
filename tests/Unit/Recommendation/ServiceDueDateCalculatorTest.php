<?php

use App\Enums\RecommendationStatus;
use App\Services\Recommendation\ServiceDueDateCalculator;

it('selects the earlier kilometer projection as the due date', function () {
    $result = (new ServiceDueDateCalculator)->calculate(
        kmSinceService: 3500,
        intervalKm: 4000,
        averageDailyKm: 50,
        baselineServiceDate: new DateTimeImmutable('2026-01-01'),
        intervalDays: 120,
        calculatedAt: new DateTimeImmutable('2026-03-01'),
    );

    expect($result['estimated_due_by_km']->format('Y-m-d'))->toBe('2026-03-11')
        ->and($result['estimated_due_by_time']->format('Y-m-d'))->toBe('2026-05-01')
        ->and($result['estimated_due_date']->format('Y-m-d'))->toBe('2026-03-11');
});

it('selects the earlier time projection and derives a bounded recommendation window', function () {
    $calculator = new ServiceDueDateCalculator;
    $result = $calculator->calculate(
        kmSinceService: 1000,
        intervalKm: 4000,
        averageDailyKm: 10,
        baselineServiceDate: new DateTimeImmutable('2026-01-01'),
        intervalDays: 120,
        calculatedAt: new DateTimeImmutable('2026-04-25'),
    );
    $window = $calculator->recommendationWindow(
        RecommendationStatus::Urgent,
        $result['estimated_due_date'],
        new DateTimeImmutable('2026-04-25'),
    );

    expect($result['estimated_due_date']->format('Y-m-d'))->toBe('2026-05-01')
        ->and($window['recommended_from_date']->format('Y-m-d'))->toBe('2026-04-25')
        ->and($window['recommended_to_date']->format('Y-m-d'))->toBe('2026-05-02')
        ->and($window['recommended_date']->format('Y-m-d'))->toBe('2026-05-01');
});
