<?php

namespace App\DTOs\Recommendation;

use App\DTOs\Fuzzy\FuzzyResult;
use App\Enums\RecommendationStatus;
use DateTimeImmutable;

final readonly class RecommendationResult
{
    public function __construct(
        public int $currentOdometer,
        public int $baselineOdometer,
        public DateTimeImmutable $baselineServiceDate,
        public int $intervalKm,
        public int $intervalDays,
        public int $kmSinceService,
        public int $daysSinceService,
        public float $averageDailyKm,
        public float $baselineDailyUsage,
        public float $progressKm,
        public float $progressTime,
        public float $usageIntensity,
        public bool $usageFallbackApplied,
        public FuzzyResult $fuzzyResult,
        public RecommendationStatus $fuzzyStatus,
        public ?DateTimeImmutable $estimatedDueByKm,
        public DateTimeImmutable $estimatedDueByTime,
        public DateTimeImmutable $estimatedDueDate,
        public ?DateTimeImmutable $recommendedFromDate,
        public ?DateTimeImmutable $recommendedToDate,
        public DateTimeImmutable $recommendedDate,
        public RecommendationStatus $finalStatus,
        public bool $guardApplied,
        public ?string $guardReason,
        public DateTimeImmutable $calculatedAt,
    ) {}
}
