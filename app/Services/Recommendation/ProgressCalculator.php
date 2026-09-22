<?php

namespace App\Services\Recommendation;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use InvalidArgumentException;

class ProgressCalculator
{
    /**
     * @return array{km_since_service: int, days_since_service: int, progress_km: float, progress_time: float}
     */
    public function calculate(
        int $currentOdometer,
        int $baselineOdometer,
        DateTimeInterface $baselineServiceDate,
        DateTimeInterface $calculatedAt,
        int $intervalKm,
        int $intervalDays,
    ): array {
        if ($intervalKm <= 0 || $intervalDays <= 0) {
            throw new InvalidArgumentException('Service intervals must be greater than zero.');
        }

        if ($currentOdometer < $baselineOdometer) {
            throw new DomainException('Current odometer cannot be lower than the service baseline.');
        }

        $baselineDate = $this->dateOnly($baselineServiceDate);
        $calculationDate = $this->dateOnly($calculatedAt);

        if ($calculationDate < $baselineDate) {
            throw new DomainException('Calculation date cannot precede the service baseline date.');
        }

        $kmSinceService = $currentOdometer - $baselineOdometer;
        $daysSinceService = (int) $baselineDate->diff($calculationDate)->days;

        return [
            'km_since_service' => $kmSinceService,
            'days_since_service' => $daysSinceService,
            'progress_km' => ($kmSinceService / $intervalKm) * 100,
            'progress_time' => ($daysSinceService / $intervalDays) * 100,
        ];
    }

    private function dateOnly(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
    }
}
