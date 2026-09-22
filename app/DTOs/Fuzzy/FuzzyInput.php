<?php

namespace App\DTOs\Fuzzy;

use InvalidArgumentException;

final readonly class FuzzyInput
{
    public function __construct(
        public float $progressKm,
        public float $progressTime,
        public float $usageIntensity,
    ) {
        foreach (get_object_vars($this) as $name => $value) {
            if (! is_finite($value) || $value < 0) {
                throw new InvalidArgumentException("{$name} must be a finite non-negative number.");
            }
        }
    }
}
