<?php

use App\DTOs\Fuzzy\RuleResult;
use App\Exceptions\Fuzzy\DefuzzificationException;
use App\Services\Fuzzy\Defuzzifier;

it('calculates the exact alpha weighted average', function () {
    $rules = [
        new RuleResult(1, 'R01', 'safe', 'safe', 'normal', 'not_urgent', 0.25, 75, 18.75),
        new RuleResult(8, 'R08', 'approaching', 'safe', 'intensive', 'urgent', 0.75, 75, 56.25),
    ];

    expect((new Defuzzifier)->defuzzify($rules))->toBe(75.0);
});

it('throws an explicit exception instead of dividing by zero', function () {
    $rules = [
        new RuleResult(1, 'R01', 'safe', 'safe', 'normal', 'not_urgent', 0, 100, 0),
    ];

    (new Defuzzifier)->defuzzify($rules);
})->throws(DefuzzificationException::class, 'denominator is zero');
