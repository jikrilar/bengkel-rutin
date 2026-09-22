<?php

use App\DTOs\Fuzzy\FuzzyInput;
use App\Exceptions\Fuzzy\InvalidFuzzyConfigurationException;
use App\Services\Fuzzy\MembershipCalculator;

function calculateMemberships(float $km, float $time, float $usage)
{
    $config = defaultFuzzyConfiguration();

    return (new MembershipCalculator)->calculate(
        new FuzzyInput($km, $time, $usage),
        $config['progress_safe_end'],
        $config['progress_approaching_peak'],
        $config['progress_critical_full'],
        $config['usage_normal_full_until'],
        $config['usage_intensive_full_from'],
    );
}

it('matches every locked progress boundary without gaps', function (
    float $value,
    float $safe,
    float $approaching,
    float $critical,
) {
    $memberships = calculateMemberships($value, $value, 100);

    expect($memberships->kmSafe)->toEqualWithDelta($safe, 0.000001)
        ->and($memberships->kmApproaching)->toEqualWithDelta($approaching, 0.000001)
        ->and($memberships->kmCritical)->toEqualWithDelta($critical, 0.000001)
        ->and($memberships->timeSafe)->toEqualWithDelta($safe, 0.000001)
        ->and($memberships->timeApproaching)->toEqualWithDelta($approaching, 0.000001)
        ->and($memberships->timeCritical)->toEqualWithDelta($critical, 0.000001);
})->with([
    'below safe boundary' => [20, 1, 0, 0],
    'safe boundary 70' => [70, 1, 0, 0],
    'safe approaching overlap' => [80, 0.5, 0.5, 0],
    'approaching peak 90' => [90, 0, 1, 0],
    'approaching critical overlap' => [95, 0, 0.5, 0.5],
    'critical boundary 100' => [100, 0, 0, 1],
    'beyond critical' => [110, 0, 0, 1],
]);

it('matches usage boundaries 80 and 120 continuously', function () {
    $at80 = calculateMemberships(50, 50, 80);
    $at100 = calculateMemberships(50, 50, 100);
    $at120 = calculateMemberships(50, 50, 120);

    expect($at80->usageNormal)->toBe(1.0)
        ->and($at80->usageIntensive)->toBe(0.0)
        ->and($at100->usageNormal)->toBe(0.5)
        ->and($at100->usageIntensive)->toBe(0.5)
        ->and($at120->usageNormal)->toBe(0.0)
        ->and($at120->usageIntensive)->toBe(1.0);
});

it('always returns memberships in the zero to one range', function () {
    foreach (range(0, 160) as $value) {
        foreach (calculateMemberships($value, $value, $value)->toArray() as $degree) {
            expect($degree)->toBeGreaterThanOrEqual(0.0)->toBeLessThanOrEqual(1.0);
        }
    }
});

it('rejects an invalid boundary configuration explicitly', function () {
    (new MembershipCalculator)->calculate(
        new FuzzyInput(50, 50, 100),
        90,
        70,
        100,
        80,
        120,
    );
})->throws(InvalidFuzzyConfigurationException::class);
