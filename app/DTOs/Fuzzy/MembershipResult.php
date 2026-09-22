<?php

namespace App\DTOs\Fuzzy;

use InvalidArgumentException;

final readonly class MembershipResult
{
    public function __construct(
        public float $kmSafe,
        public float $kmApproaching,
        public float $kmCritical,
        public float $timeSafe,
        public float $timeApproaching,
        public float $timeCritical,
        public float $usageNormal,
        public float $usageIntensive,
    ) {
        foreach (get_object_vars($this) as $name => $value) {
            if (! is_finite($value) || $value < 0 || $value > 1) {
                throw new InvalidArgumentException("{$name} membership must be between 0 and 1.");
            }
        }
    }

    public function kilometer(string $state): float
    {
        return match ($state) {
            'safe' => $this->kmSafe,
            'approaching' => $this->kmApproaching,
            'critical' => $this->kmCritical,
            default => throw new InvalidArgumentException("Unknown kilometer state [{$state}]."),
        };
    }

    public function time(string $state): float
    {
        return match ($state) {
            'safe' => $this->timeSafe,
            'approaching' => $this->timeApproaching,
            'critical' => $this->timeCritical,
            default => throw new InvalidArgumentException("Unknown time state [{$state}]."),
        };
    }

    public function usage(string $state): float
    {
        return match ($state) {
            'normal' => $this->usageNormal,
            'intensive' => $this->usageIntensive,
            default => throw new InvalidArgumentException("Unknown usage state [{$state}]."),
        };
    }

    /** @return array<string, float> */
    public function toArray(): array
    {
        return [
            'km_safe' => $this->kmSafe,
            'km_approaching' => $this->kmApproaching,
            'km_critical' => $this->kmCritical,
            'time_safe' => $this->timeSafe,
            'time_approaching' => $this->timeApproaching,
            'time_critical' => $this->timeCritical,
            'usage_normal' => $this->usageNormal,
            'usage_intensive' => $this->usageIntensive,
        ];
    }
}
