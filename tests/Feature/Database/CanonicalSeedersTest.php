<?php

use App\Enums\UserRole;
use App\Models\FuzzyConfig;
use App\Models\FuzzyRule;
use App\Models\OperatingHour;
use App\Models\ServiceProfile;
use App\Models\User;
use App\Models\WorkshopSetting;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FuzzyConfigSeeder;
use Database\Seeders\FuzzyRuleSeeder;

it('seeds exactly the 18 canonical fuzzy rules idempotently', function () {
    $this->seed(FuzzyRuleSeeder::class);
    $this->seed(FuzzyRuleSeeder::class);

    $expected = [
        'R01' => ['safe', 'safe', 'normal', 'not_urgent'],
        'R02' => ['safe', 'safe', 'intensive', 'not_urgent'],
        'R03' => ['safe', 'approaching', 'normal', 'not_urgent'],
        'R04' => ['safe', 'approaching', 'intensive', 'urgent'],
        'R05' => ['safe', 'critical', 'normal', 'urgent'],
        'R06' => ['safe', 'critical', 'intensive', 'urgent'],
        'R07' => ['approaching', 'safe', 'normal', 'not_urgent'],
        'R08' => ['approaching', 'safe', 'intensive', 'urgent'],
        'R09' => ['approaching', 'approaching', 'normal', 'urgent'],
        'R10' => ['approaching', 'approaching', 'intensive', 'urgent'],
        'R11' => ['approaching', 'critical', 'normal', 'urgent'],
        'R12' => ['approaching', 'critical', 'intensive', 'urgent'],
        'R13' => ['critical', 'safe', 'normal', 'urgent'],
        'R14' => ['critical', 'safe', 'intensive', 'urgent'],
        'R15' => ['critical', 'approaching', 'normal', 'urgent'],
        'R16' => ['critical', 'approaching', 'intensive', 'urgent'],
        'R17' => ['critical', 'critical', 'normal', 'urgent'],
        'R18' => ['critical', 'critical', 'intensive', 'urgent'],
    ];

    $actual = FuzzyRule::query()
        ->orderBy('code')
        ->get()
        ->mapWithKeys(fn (FuzzyRule $rule): array => [
            $rule->code => [
                $rule->km_state,
                $rule->time_state,
                $rule->usage_state,
                $rule->consequent,
            ],
        ])
        ->all();

    expect(FuzzyRule::query()->count())->toBe(18)
        ->and($actual)->toBe($expected);
});

it('keeps exactly one fuzzy config active when reseeded', function () {
    $this->seed(FuzzyConfigSeeder::class);
    FuzzyConfig::factory()->active()->create(['version' => 2]);
    FuzzyConfig::factory()->active()->create(['version' => 3]);

    $this->seed(FuzzyConfigSeeder::class);

    expect(FuzzyConfig::query()->active()->count())->toBe(1)
        ->and(FuzzyConfig::query()->active()->value('version'))->toBe(3);
});

it('seeds the complete operational baseline idempotently', function () {
    config()->set([
        'admin.name' => 'Admin Test',
        'admin.email' => 'admin-seed@example.test',
        'admin.phone' => '081200000000',
        'admin.password' => 'password',
    ]);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'admin-seed@example.test')->where('role', UserRole::Admin)->count())->toBe(1)
        ->and(ServiceProfile::query()->where('name', 'Servis Rutin Standar')->count())->toBe(1)
        ->and(WorkshopSetting::query()->count())->toBe(1)
        ->and(OperatingHour::query()->count())->toBe(7)
        ->and(OperatingHour::query()->where('is_open', true)->count())->toBe(6)
        ->and(FuzzyRule::query()->count())->toBe(18)
        ->and(FuzzyConfig::query()->active()->count())->toBe(1);
});
