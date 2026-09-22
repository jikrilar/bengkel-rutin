<?php

use App\DTOs\Fuzzy\MembershipResult;
use App\Services\Fuzzy\RuleEvaluator;

it('evaluates all 18 rules and applies min alpha to the correct active rules', function () {
    $memberships = new MembershipResult(
        kmSafe: 0.75,
        kmApproaching: 0.25,
        kmCritical: 0,
        timeSafe: 1,
        timeApproaching: 0,
        timeCritical: 0,
        usageNormal: 1,
        usageIntensive: 0,
    );

    $results = (new RuleEvaluator)->evaluate($memberships, canonicalFuzzyRules());
    $active = collect($results)->filter->isActive()->values();

    expect($results)->toHaveCount(18)
        ->and($active->pluck('code')->all())->toBe(['R01', 'R07'])
        ->and($active[0]->alpha)->toBe(0.75)
        ->and($active[1]->alpha)->toBe(0.25);
});

it('uses monotonic Tsukamoto consequents and inverse z instead of Sugeno constants', function () {
    $evaluator = new RuleEvaluator;

    $lowAlpha = new MembershipResult(0.2, 0.8, 0, 1, 0, 0, 1, 0);
    $highAlpha = new MembershipResult(0.8, 0.2, 0, 1, 0, 0, 1, 0);
    $notUrgentRule = [[
        'id' => 1,
        'code' => 'N01',
        'km_state' => 'safe',
        'time_state' => 'safe',
        'usage_state' => 'normal',
        'consequent' => 'not_urgent',
    ]];
    $urgentRule = [[
        'id' => 2,
        'code' => 'U01',
        'km_state' => 'safe',
        'time_state' => 'safe',
        'usage_state' => 'normal',
        'consequent' => 'urgent',
    ]];

    $notUrgentLow = $evaluator->evaluate($lowAlpha, $notUrgentRule)[0];
    $notUrgentHigh = $evaluator->evaluate($highAlpha, $notUrgentRule)[0];
    $urgentLow = $evaluator->evaluate($lowAlpha, $urgentRule)[0];
    $urgentHigh = $evaluator->evaluate($highAlpha, $urgentRule)[0];

    expect($notUrgentLow->zValue)->toBe(80.0)
        ->and($notUrgentHigh->zValue)->toEqualWithDelta(20.0, 0.000001)
        ->and($urgentLow->zValue)->toBe(20.0)
        ->and($urgentHigh->zValue)->toBe(80.0)
        ->and($urgentHigh->weightedValue)->toBe(64.0);
});
