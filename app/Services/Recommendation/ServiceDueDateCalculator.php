<?php

namespace App\Services\Recommendation;

use App\Enums\RecommendationStatus;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class ServiceDueDateCalculator
{
    /**
     * @return array{estimated_due_by_km: ?DateTimeImmutable, estimated_due_by_time: DateTimeImmutable, estimated_due_date: DateTimeImmutable}
     */
    public function calculate(
        int $kmSinceService,
        int $intervalKm,
        float $averageDailyKm,
        DateTimeInterface $baselineServiceDate,
        int $intervalDays,
        DateTimeInterface $calculatedAt,
    ): array {
        if ($intervalKm <= 0 || $intervalDays <= 0 || $averageDailyKm < 0) {
            throw new InvalidArgumentException('Due-date inputs must use positive intervals and non-negative usage.');
        }

        $calculationDate = $this->dateOnly($calculatedAt);
        $remainingKm = max(0, $intervalKm - $kmSinceService);
        $estimatedDueByKm = null;

        if ($averageDailyKm > 0) {
            $estimatedDaysByKm = (int) ceil($remainingKm / $averageDailyKm);
            $estimatedDueByKm = $calculationDate->modify("+{$estimatedDaysByKm} days");
        }

        $estimatedDueByTime = $this->dateOnly($baselineServiceDate)->modify("+{$intervalDays} days");
        $estimatedDueDate = $estimatedDueByKm === null || $estimatedDueByTime < $estimatedDueByKm
            ? $estimatedDueByTime
            : $estimatedDueByKm;

        return [
            'estimated_due_by_km' => $estimatedDueByKm,
            'estimated_due_by_time' => $estimatedDueByTime,
            'estimated_due_date' => $estimatedDueDate,
        ];
    }

    /**
     * @return array{recommended_from_date: ?DateTimeImmutable, recommended_to_date: ?DateTimeImmutable, recommended_date: DateTimeImmutable}
     */
    public function recommendationWindow(
        RecommendationStatus $status,
        DateTimeInterface $estimatedDueDate,
        DateTimeInterface $calculatedAt,
    ): array {
        $calculationDate = $this->dateOnly($calculatedAt);
        $dueDate = $this->dateOnly($estimatedDueDate);

        [$from, $to] = match ($status) {
            RecommendationStatus::Urgent => [$calculationDate, $calculationDate->modify('+7 days')],
            RecommendationStatus::Approaching => [
                $calculationDate->modify('+8 days'),
                $calculationDate->modify('+30 days'),
            ],
            RecommendationStatus::NotNeeded => [$calculationDate->modify('+31 days'), null],
            RecommendationStatus::Unavailable => [null, null],
        };

        if ($from === null) {
            throw new InvalidArgumentException('Unavailable recommendations do not have a service window.');
        }

        $recommendedDate = $dueDate < $from ? $from : $dueDate;

        if ($to !== null && $recommendedDate > $to) {
            $recommendedDate = $to;
        }

        return [
            'recommended_from_date' => $from,
            'recommended_to_date' => $to,
            'recommended_date' => $recommendedDate,
        ];
    }

    private function dateOnly(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
    }
}
