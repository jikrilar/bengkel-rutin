<?php

namespace App\DTOs\Fuzzy;

use InvalidArgumentException;

final readonly class FuzzyResult
{
    /**
     * @param  list<RuleResult>  $ruleResults
     */
    public function __construct(
        public MembershipResult $memberships,
        public array $ruleResults,
        public float $score,
    ) {
        if (! is_finite($score) || $score < 0 || $score > 100) {
            throw new InvalidArgumentException('Fuzzy score must be between 0 and 100.');
        }
    }

    /** @return list<RuleResult> */
    public function activeRuleResults(): array
    {
        return array_values(array_filter(
            $this->ruleResults,
            static fn (RuleResult $result): bool => $result->isActive(),
        ));
    }
}
