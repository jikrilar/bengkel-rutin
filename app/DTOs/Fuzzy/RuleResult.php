<?php

namespace App\DTOs\Fuzzy;

use InvalidArgumentException;

final readonly class RuleResult
{
    public function __construct(
        public int $ruleId,
        public string $code,
        public string $kmState,
        public string $timeState,
        public string $usageState,
        public string $consequent,
        public float $alpha,
        public float $zValue,
        public float $weightedValue,
    ) {
        if (! is_finite($alpha) || $alpha < 0 || $alpha > 1) {
            throw new InvalidArgumentException('Rule alpha must be between 0 and 1.');
        }

        if (! is_finite($zValue) || $zValue < 0 || $zValue > 100) {
            throw new InvalidArgumentException('Rule z value must be between 0 and 100.');
        }

        if (! is_finite($weightedValue) || $weightedValue < 0) {
            throw new InvalidArgumentException('Rule weighted value must be finite and non-negative.');
        }
    }

    public function isActive(): bool
    {
        return $this->alpha > 0;
    }
}
