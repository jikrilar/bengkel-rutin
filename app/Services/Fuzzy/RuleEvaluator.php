<?php

namespace App\Services\Fuzzy;

use App\DTOs\Fuzzy\MembershipResult;
use App\DTOs\Fuzzy\RuleResult;
use App\Exceptions\Fuzzy\InvalidFuzzyRuleSetException;

class RuleEvaluator
{
    /**
     * @param  list<array{id: int, code: string, km_state: string, time_state: string, usage_state: string, consequent: string}>  $rules
     * @return list<RuleResult>
     */
    public function evaluate(MembershipResult $memberships, array $rules): array
    {
        return array_map(function (array $rule) use ($memberships): RuleResult {
            $this->validateRule($rule);

            $alpha = min(
                $memberships->kilometer($rule['km_state']),
                $memberships->time($rule['time_state']),
                $memberships->usage($rule['usage_state']),
            );
            $zValue = $this->inverseConsequent($rule['consequent'], $alpha);

            return new RuleResult(
                ruleId: $rule['id'],
                code: $rule['code'],
                kmState: $rule['km_state'],
                timeState: $rule['time_state'],
                usageState: $rule['usage_state'],
                consequent: $rule['consequent'],
                alpha: $alpha,
                zValue: $zValue,
                weightedValue: $alpha * $zValue,
            );
        }, $rules);
    }

    private function inverseConsequent(string $consequent, float $alpha): float
    {
        return match ($consequent) {
            // Monotonically decreasing set: mu_not_urgent(z) = (100 - z) / 100.
            'not_urgent' => 100 * (1 - $alpha),
            // Monotonically increasing set: mu_urgent(z) = z / 100.
            'urgent' => 100 * $alpha,
            default => throw new InvalidFuzzyRuleSetException(
                "Unknown consequent [{$consequent}].",
            ),
        };
    }

    /** @param array<string, mixed> $rule */
    private function validateRule(array $rule): void
    {
        foreach (['id', 'code', 'km_state', 'time_state', 'usage_state', 'consequent'] as $key) {
            if (! array_key_exists($key, $rule)) {
                throw new InvalidFuzzyRuleSetException("Rule field [{$key}] is required.");
            }
        }
    }
}
