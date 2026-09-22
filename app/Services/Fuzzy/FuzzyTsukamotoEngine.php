<?php

namespace App\Services\Fuzzy;

use App\DTOs\Fuzzy\FuzzyInput;
use App\DTOs\Fuzzy\FuzzyResult;

class FuzzyTsukamotoEngine
{
    public function __construct(
        private readonly MembershipCalculator $membershipCalculator,
        private readonly RuleEvaluator $ruleEvaluator,
        private readonly Defuzzifier $defuzzifier,
    ) {}

    /**
     * @param  array{progress_safe_end: float, progress_approaching_peak: float, progress_critical_full: float, usage_normal_full_until: float, usage_intensive_full_from: float}  $configuration
     * @param  list<array{id: int, code: string, km_state: string, time_state: string, usage_state: string, consequent: string}>  $rules
     */
    public function calculate(FuzzyInput $input, array $configuration, array $rules): FuzzyResult
    {
        $memberships = $this->membershipCalculator->calculate(
            $input,
            $configuration['progress_safe_end'],
            $configuration['progress_approaching_peak'],
            $configuration['progress_critical_full'],
            $configuration['usage_normal_full_until'],
            $configuration['usage_intensive_full_from'],
        );
        $ruleResults = $this->ruleEvaluator->evaluate($memberships, $rules);

        return new FuzzyResult(
            memberships: $memberships,
            ruleResults: $ruleResults,
            score: $this->defuzzifier->defuzzify($ruleResults),
        );
    }
}
