<?php

namespace App\Services\Fuzzy;

use App\DTOs\Fuzzy\FuzzyInput;
use App\DTOs\Fuzzy\MembershipResult;
use App\Exceptions\Fuzzy\InvalidFuzzyConfigurationException;

class MembershipCalculator
{
    public function calculate(
        FuzzyInput $input,
        float $progressSafeEnd,
        float $progressApproachingPeak,
        float $progressCriticalFull,
        float $usageNormalFullUntil,
        float $usageIntensiveFullFrom,
    ): MembershipResult {
        $this->validateBoundaries(
            $progressSafeEnd,
            $progressApproachingPeak,
            $progressCriticalFull,
            $usageNormalFullUntil,
            $usageIntensiveFullFrom,
        );

        $km = $this->progressMemberships(
            $input->progressKm,
            $progressSafeEnd,
            $progressApproachingPeak,
            $progressCriticalFull,
        );
        $time = $this->progressMemberships(
            $input->progressTime,
            $progressSafeEnd,
            $progressApproachingPeak,
            $progressCriticalFull,
        );
        $usage = $this->usageMemberships(
            $input->usageIntensity,
            $usageNormalFullUntil,
            $usageIntensiveFullFrom,
        );

        return new MembershipResult(
            kmSafe: $km['safe'],
            kmApproaching: $km['approaching'],
            kmCritical: $km['critical'],
            timeSafe: $time['safe'],
            timeApproaching: $time['approaching'],
            timeCritical: $time['critical'],
            usageNormal: $usage['normal'],
            usageIntensive: $usage['intensive'],
        );
    }

    /** @return array{safe: float, approaching: float, critical: float} */
    private function progressMemberships(float $value, float $safeEnd, float $peak, float $criticalFull): array
    {
        $safe = match (true) {
            $value <= $safeEnd => 1.0,
            $value < $peak => ($peak - $value) / ($peak - $safeEnd),
            default => 0.0,
        };

        $approaching = match (true) {
            $value <= $safeEnd => 0.0,
            $value < $peak => ($value - $safeEnd) / ($peak - $safeEnd),
            $value === $peak => 1.0,
            $value < $criticalFull => ($criticalFull - $value) / ($criticalFull - $peak),
            default => 0.0,
        };

        $critical = match (true) {
            $value <= $peak => 0.0,
            $value < $criticalFull => ($value - $peak) / ($criticalFull - $peak),
            default => 1.0,
        };

        return [
            'safe' => $this->clampDegree($safe),
            'approaching' => $this->clampDegree($approaching),
            'critical' => $this->clampDegree($critical),
        ];
    }

    /** @return array{normal: float, intensive: float} */
    private function usageMemberships(float $value, float $normalUntil, float $intensiveFrom): array
    {
        if ($value <= $normalUntil) {
            return ['normal' => 1.0, 'intensive' => 0.0];
        }

        if ($value >= $intensiveFrom) {
            return ['normal' => 0.0, 'intensive' => 1.0];
        }

        $intensive = ($value - $normalUntil) / ($intensiveFrom - $normalUntil);

        return [
            'normal' => $this->clampDegree(1 - $intensive),
            'intensive' => $this->clampDegree($intensive),
        ];
    }

    private function clampDegree(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    private function validateBoundaries(
        float $safeEnd,
        float $approachingPeak,
        float $criticalFull,
        float $normalUntil,
        float $intensiveFrom,
    ): void {
        if (! ($safeEnd < $approachingPeak && $approachingPeak < $criticalFull)) {
            throw new InvalidFuzzyConfigurationException(
                'Progress boundaries must satisfy safe < approaching < critical.',
            );
        }

        if ($normalUntil >= $intensiveFrom) {
            throw new InvalidFuzzyConfigurationException(
                'Usage boundaries must satisfy normal < intensive.',
            );
        }
    }
}
