<?php

namespace Database\Seeders;

use App\Models\FuzzyRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuzzyRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['R01', 'safe', 'safe', 'normal', 'not_urgent'],
            ['R02', 'safe', 'safe', 'intensive', 'not_urgent'],
            ['R03', 'safe', 'approaching', 'normal', 'not_urgent'],
            ['R04', 'safe', 'approaching', 'intensive', 'urgent'],
            ['R05', 'safe', 'critical', 'normal', 'urgent'],
            ['R06', 'safe', 'critical', 'intensive', 'urgent'],
            ['R07', 'approaching', 'safe', 'normal', 'not_urgent'],
            ['R08', 'approaching', 'safe', 'intensive', 'urgent'],
            ['R09', 'approaching', 'approaching', 'normal', 'urgent'],
            ['R10', 'approaching', 'approaching', 'intensive', 'urgent'],
            ['R11', 'approaching', 'critical', 'normal', 'urgent'],
            ['R12', 'approaching', 'critical', 'intensive', 'urgent'],
            ['R13', 'critical', 'safe', 'normal', 'urgent'],
            ['R14', 'critical', 'safe', 'intensive', 'urgent'],
            ['R15', 'critical', 'approaching', 'normal', 'urgent'],
            ['R16', 'critical', 'approaching', 'intensive', 'urgent'],
            ['R17', 'critical', 'critical', 'normal', 'urgent'],
            ['R18', 'critical', 'critical', 'intensive', 'urgent'],
        ];

        $now = now();
        $payload = array_map(
            fn (array $rule): array => [
                'code' => $rule[0],
                'km_state' => $rule[1],
                'time_state' => $rule[2],
                'usage_state' => $rule[3],
                'consequent' => $rule[4],
                'description' => sprintf(
                    'KM %s, waktu %s, penggunaan %s menghasilkan %s.',
                    $rule[1],
                    $rule[2],
                    $rule[3],
                    $rule[4],
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $rules,
        );

        DB::transaction(function () use ($payload): void {
            FuzzyRule::query()->upsert(
                $payload,
                ['code'],
                ['km_state', 'time_state', 'usage_state', 'consequent', 'description', 'updated_at'],
            );

            FuzzyRule::query()
                ->whereNotIn('code', array_column($payload, 'code'))
                ->delete();
        });
    }
}
