<?php

use App\DTOs\Fuzzy\FuzzyInput;
use App\Services\Fuzzy\Defuzzifier;
use App\Services\Fuzzy\FuzzyTsukamotoEngine;
use App\Services\Fuzzy\MembershipCalculator;
use App\Services\Fuzzy\RuleEvaluator;

function fuzzyEngine(): FuzzyTsukamotoEngine
{
    return new FuzzyTsukamotoEngine(
        new MembershipCalculator,
        new RuleEvaluator,
        new Defuzzifier,
    );
}

it('is deterministic and bounded for the locked scenario matrix', function (
    float $progressKm,
    float $progressTime,
    float $usageIntensity,
) {
    $input = new FuzzyInput($progressKm, $progressTime, $usageIntensity);
    $engine = fuzzyEngine();

    $first = $engine->calculate($input, defaultFuzzyConfiguration(), canonicalFuzzyRules());
    $second = $engine->calculate($input, defaultFuzzyConfiguration(), canonicalFuzzyRules());
    $activeAlpha = array_sum(array_map(
        static fn ($rule): float => $rule->alpha,
        $first->activeRuleResults(),
    ));
    $activeWeighted = array_sum(array_map(
        static fn ($rule): float => $rule->weightedValue,
        $first->activeRuleResults(),
    ));

    expect($first->score)->toEqualWithDelta($second->score, 0.000000001)
        ->and($first->score)->toBeGreaterThanOrEqual(0.0)->toBeLessThanOrEqual(100.0)
        ->and($first->ruleResults)->toHaveCount(18)
        ->and($first->activeRuleResults())->not->toBeEmpty()
        ->and($first->score)->toEqualWithDelta($activeWeighted / $activeAlpha, 0.000000001);
})->with([
    '20 / 20 / normal' => [20, 20, 60],
    '20 / 20 / intensive' => [20, 20, 140],
    '75 / 40 / normal' => [75, 40, 60],
    '75 / 40 / intensive' => [75, 40, 140],
    '80 / 80 / normal' => [80, 80, 60],
    '80 / 80 / intensive' => [80, 80, 140],
    '95 / 50 / normal' => [95, 50, 60],
    '50 / 95 / normal' => [50, 95, 60],
    '100 / 30 / normal' => [100, 30, 60],
    '30 / 100 / normal' => [30, 100, 60],
    '110 / 110 / intensive' => [110, 110, 140],
]);

it('activates only R01 for fully safe normal input', function () {
    $result = fuzzyEngine()->calculate(
        new FuzzyInput(20, 20, 60),
        defaultFuzzyConfiguration(),
        canonicalFuzzyRules(),
    );

    expect(array_column($result->activeRuleResults(), 'code'))->toBe(['R01'])
        ->and($result->score)->toBe(0.0);
});

it('activates only R18 for fully critical intensive input', function () {
    $result = fuzzyEngine()->calculate(
        new FuzzyInput(110, 110, 140),
        defaultFuzzyConfiguration(),
        canonicalFuzzyRules(),
    );

    expect(array_column($result->activeRuleResults(), 'code'))->toBe(['R18'])
        ->and($result->score)->toBe(100.0);
});

it('keeps a newly serviced vehicle below the approaching threshold with neutral usage fallback', function () {
    $result = fuzzyEngine()->calculate(
        new FuzzyInput(0, 0, 100),
        defaultFuzzyConfiguration(),
        canonicalFuzzyRules(),
    );

    expect(array_column($result->activeRuleResults(), 'code'))->toBe(['R01', 'R02'])
        ->and($result->score)->toBe(20.0)
        ->and($result->score)->toBeLessThan(40.0);
});

it('keeps critical progress urgent with neutral usage fallback', function () {
    $result = fuzzyEngine()->calculate(
        new FuzzyInput(110, 110, 100),
        defaultFuzzyConfiguration(),
        canonicalFuzzyRules(),
    );

    expect(array_column($result->activeRuleResults(), 'code'))->toBe(['R17', 'R18'])
        ->and($result->score)->toBe(70.0);
});
