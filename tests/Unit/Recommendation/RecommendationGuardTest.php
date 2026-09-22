<?php

use App\Enums\RecommendationStatus;
use App\Services\Recommendation\RecommendationGuard;

it('escalates when the projected due date is more urgent', function () {
    $fuzzyScore = 68.4;
    $result = (new RecommendationGuard)->apply(
        RecommendationStatus::Approaching,
        new DateTimeImmutable('2026-05-06'),
        new DateTimeImmutable('2026-05-01'),
    );

    expect($result['final_status'])->toBe(RecommendationStatus::Urgent)
        ->and($result['guard_applied'])->toBeTrue()
        ->and($result['guard_reason'])->toContain('5 day(s)')
        ->and($fuzzyScore)->toBe(68.4);
});

it('never de-escalates a fuzzy status', function () {
    $result = (new RecommendationGuard)->apply(
        RecommendationStatus::Urgent,
        new DateTimeImmutable('2026-12-01'),
        new DateTimeImmutable('2026-05-01'),
    );

    expect($result['final_status'])->toBe(RecommendationStatus::Urgent)
        ->and($result['guard_applied'])->toBeFalse()
        ->and($result['guard_reason'])->toBeNull();
});

it('maps fuzzy score boundaries to the locked statuses', function () {
    expect(RecommendationStatus::fromScore(0))->toBe(RecommendationStatus::NotNeeded)
        ->and(RecommendationStatus::fromScore(39.999))->toBe(RecommendationStatus::NotNeeded)
        ->and(RecommendationStatus::fromScore(40))->toBe(RecommendationStatus::Approaching)
        ->and(RecommendationStatus::fromScore(69.999))->toBe(RecommendationStatus::Approaching)
        ->and(RecommendationStatus::fromScore(70))->toBe(RecommendationStatus::Urgent)
        ->and(RecommendationStatus::fromScore(100))->toBe(RecommendationStatus::Urgent);
});
