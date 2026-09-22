<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * @return list<array{id: int, code: string, km_state: string, time_state: string, usage_state: string, consequent: string}>
 */
function canonicalFuzzyRules(): array
{
    $rules = [
        ['safe', 'safe', 'normal', 'not_urgent'],
        ['safe', 'safe', 'intensive', 'not_urgent'],
        ['safe', 'approaching', 'normal', 'not_urgent'],
        ['safe', 'approaching', 'intensive', 'urgent'],
        ['safe', 'critical', 'normal', 'urgent'],
        ['safe', 'critical', 'intensive', 'urgent'],
        ['approaching', 'safe', 'normal', 'not_urgent'],
        ['approaching', 'safe', 'intensive', 'urgent'],
        ['approaching', 'approaching', 'normal', 'urgent'],
        ['approaching', 'approaching', 'intensive', 'urgent'],
        ['approaching', 'critical', 'normal', 'urgent'],
        ['approaching', 'critical', 'intensive', 'urgent'],
        ['critical', 'safe', 'normal', 'urgent'],
        ['critical', 'safe', 'intensive', 'urgent'],
        ['critical', 'approaching', 'normal', 'urgent'],
        ['critical', 'approaching', 'intensive', 'urgent'],
        ['critical', 'critical', 'normal', 'urgent'],
        ['critical', 'critical', 'intensive', 'urgent'],
    ];

    return array_map(
        static fn (array $rule, int $index): array => [
            'id' => $index + 1,
            'code' => sprintf('R%02d', $index + 1),
            'km_state' => $rule[0],
            'time_state' => $rule[1],
            'usage_state' => $rule[2],
            'consequent' => $rule[3],
        ],
        $rules,
        array_keys($rules),
    );
}

/** @return array{progress_safe_end: float, progress_approaching_peak: float, progress_critical_full: float, usage_normal_full_until: float, usage_intensive_full_from: float} */
function defaultFuzzyConfiguration(): array
{
    return [
        'progress_safe_end' => 70.0,
        'progress_approaching_peak' => 90.0,
        'progress_critical_full' => 100.0,
        'usage_normal_full_until' => 80.0,
        'usage_intensive_full_from' => 120.0,
    ];
}
