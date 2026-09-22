<?php

namespace App\Services\Recommendation;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use InvalidArgumentException;

class UsageCalculator
{
    /**
     * The neutral fallback is intentionally deterministic: when fewer than two
     * usable readings exist, actual usage equals baseline usage (100% intensity).
     *
     * @param  list<array{odometer: int, recorded_at: DateTimeInterface}>  $history
     * @return array{average_daily_km: float, baseline_daily_usage: float, usage_intensity: float, fallback_applied: bool}
     */
    public function calculate(array $history, int $intervalKm, int $intervalDays): array
    {
        if ($intervalKm <= 0 || $intervalDays <= 0) {
            throw new InvalidArgumentException('Service intervals must be greater than zero.');
        }

        $baselineDailyUsage = (float) ($intervalKm / $intervalDays);
        usort(
            $history,
            static fn (array $left, array $right): int => $left['recorded_at'] <=> $right['recorded_at'],
        );

        if (count($history) < 2) {
            return $this->fallback($baselineDailyUsage);
        }

        $first = $history[0];
        $last = $history[array_key_last($history)];
        $firstDate = $this->dateOnly($first['recorded_at']);
        $lastDate = $this->dateOnly($last['recorded_at']);
        $dayDelta = (int) $firstDate->diff($lastDate)->days;
        $odometerDelta = $last['odometer'] - $first['odometer'];

        if ($lastDate <= $firstDate || $dayDelta === 0) {
            return $this->fallback($baselineDailyUsage);
        }

        if ($odometerDelta < 0) {
            throw new DomainException('Odometer history must be non-decreasing.');
        }

        $averageDailyKm = (float) ($odometerDelta / $dayDelta);

        return [
            'average_daily_km' => $averageDailyKm,
            'baseline_daily_usage' => $baselineDailyUsage,
            'usage_intensity' => (float) (($averageDailyKm / $baselineDailyUsage) * 100),
            'fallback_applied' => false,
        ];
    }

    /** @return array{average_daily_km: float, baseline_daily_usage: float, usage_intensity: float, fallback_applied: bool} */
    private function fallback(float $baselineDailyUsage): array
    {
        return [
            'average_daily_km' => $baselineDailyUsage,
            'baseline_daily_usage' => $baselineDailyUsage,
            'usage_intensity' => 100.0,
            'fallback_applied' => true,
        ];
    }

    private function dateOnly(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
    }
}
