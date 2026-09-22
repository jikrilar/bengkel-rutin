<?php

namespace App\Services\Fuzzy;

use App\DTOs\Fuzzy\RuleResult;
use App\Exceptions\Fuzzy\DefuzzificationException;

class Defuzzifier
{
    /** @param list<RuleResult> $ruleResults */
    public function defuzzify(array $ruleResults): float
    {
        $denominator = array_reduce(
            $ruleResults,
            static fn (float $carry, RuleResult $result): float => $carry + $result->alpha,
            0.0,
        );

        if ($denominator <= PHP_FLOAT_EPSILON) {
            throw new DefuzzificationException(
                'Defuzzification denominator is zero; no fuzzy rule is active.',
            );
        }

        $numerator = array_reduce(
            $ruleResults,
            static fn (float $carry, RuleResult $result): float => $carry + $result->weightedValue,
            0.0,
        );

        return max(0.0, min(100.0, $numerator / $denominator));
    }
}
