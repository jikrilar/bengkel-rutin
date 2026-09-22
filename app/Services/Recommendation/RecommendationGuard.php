<?php

namespace App\Services\Recommendation;

use App\Enums\RecommendationStatus;
use DateTimeImmutable;
use DateTimeInterface;

class RecommendationGuard
{
    /** @return array{final_status: RecommendationStatus, guard_applied: bool, guard_reason: ?string} */
    public function apply(
        RecommendationStatus $fuzzyStatus,
        DateTimeInterface $estimatedDueDate,
        DateTimeInterface $calculatedAt,
    ): array {
        $dueDate = $this->dateOnly($estimatedDueDate);
        $calculationDate = $this->dateOnly($calculatedAt);
        $daysUntilDue = (int) $calculationDate->diff($dueDate)->format('%r%a');

        $projectedStatus = match (true) {
            $daysUntilDue <= 7 => RecommendationStatus::Urgent,
            $daysUntilDue <= 30 => RecommendationStatus::Approaching,
            default => RecommendationStatus::NotNeeded,
        };

        if ($projectedStatus->urgencyRank() <= $fuzzyStatus->urgencyRank()) {
            return [
                'final_status' => $fuzzyStatus,
                'guard_applied' => false,
                'guard_reason' => null,
            ];
        }

        return [
            'final_status' => $projectedStatus,
            'guard_applied' => true,
            'guard_reason' => sprintf(
                'Projected service due date is %d day(s) away, requiring escalation from %s to %s.',
                $daysUntilDue,
                $fuzzyStatus->value,
                $projectedStatus->value,
            ),
        ];
    }

    private function dateOnly(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
    }
}
