<?php

namespace App\Services\Recommendation;

use App\DTOs\Fuzzy\FuzzyInput;
use App\DTOs\Recommendation\RecommendationResult;
use App\Enums\CalculationTrigger;
use App\Enums\RecommendationStatus;
use App\Events\RecommendationStatusChanged;
use App\Exceptions\Fuzzy\InvalidFuzzyRuleSetException;
use App\Exceptions\Recommendation\RecommendationUnavailableException;
use App\Models\FuzzyCalculation;
use App\Models\FuzzyRule;
use App\Models\OdometerLog;
use App\Models\Vehicle;
use App\Services\Fuzzy\FuzzyConfigResolver;
use App\Services\Fuzzy\FuzzyTsukamotoEngine;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function __construct(
        private readonly ProgressCalculator $progressCalculator,
        private readonly UsageCalculator $usageCalculator,
        private readonly FuzzyConfigResolver $configResolver,
        private readonly FuzzyTsukamotoEngine $fuzzyEngine,
        private readonly ServiceDueDateCalculator $dueDateCalculator,
        private readonly RecommendationGuard $recommendationGuard,
    ) {}

    public function calculateAndPersist(
        Vehicle $vehicle,
        CalculationTrigger $trigger,
        ?DateTimeInterface $calculatedAt = null,
    ): RecommendationResult {
        $previousStatus = FuzzyCalculation::query()
            ->where('vehicle_id', $vehicle->id)
            ->orderByDesc('calculated_at')
            ->orderByDesc('id')
            ->value('final_status');
        $previousStatus = match (true) {
            $previousStatus instanceof RecommendationStatus => $previousStatus,
            $previousStatus === null => null,
            default => RecommendationStatus::from($previousStatus),
        };

        $vehicle->load(['serviceProfile', 'latestOdometer']);

        if (
            $vehicle->serviceProfile === null
            || $vehicle->latestOdometer === null
            || $vehicle->baseline_service_date === null
            || $vehicle->baseline_odometer === null
        ) {
            throw new RecommendationUnavailableException(
                'Recommendation requires a service profile, baseline, and current odometer.',
            );
        }

        $calculationDateTime = $calculatedAt === null
            ? new DateTimeImmutable('now', new DateTimeZone(config('app.timezone')))
            : DateTimeImmutable::createFromInterface($calculatedAt);
        $baselineServiceDate = DateTimeImmutable::createFromInterface($vehicle->baseline_service_date);
        $profile = $vehicle->serviceProfile;
        $latestOdometer = $vehicle->latestOdometer;

        $progress = $this->progressCalculator->calculate(
            currentOdometer: $latestOdometer->odometer,
            baselineOdometer: $vehicle->baseline_odometer,
            baselineServiceDate: $baselineServiceDate,
            calculatedAt: $calculationDateTime,
            intervalKm: $profile->interval_km,
            intervalDays: $profile->interval_days,
        );

        $history = $vehicle->odometerLogs()
            ->where('recorded_at', '>=', $baselineServiceDate)
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->map(static fn (OdometerLog $log): array => [
                'odometer' => $log->odometer,
                'recorded_at' => DateTimeImmutable::createFromInterface($log->recorded_at),
            ])
            ->all();
        $usage = $this->usageCalculator->calculate(
            $history,
            $profile->interval_km,
            $profile->interval_days,
        );

        $config = $this->configResolver->resolve();
        $rules = FuzzyRule::query()->orderBy('code')->get();
        $this->assertCanonicalRules($rules->pluck('code')->all());

        $ruleDefinitions = $rules->map(static fn (FuzzyRule $rule): array => [
            'id' => $rule->id,
            'code' => $rule->code,
            'km_state' => $rule->km_state,
            'time_state' => $rule->time_state,
            'usage_state' => $rule->usage_state,
            'consequent' => $rule->consequent,
        ])->all();
        $fuzzyResult = $this->fuzzyEngine->calculate(
            new FuzzyInput(
                progressKm: $progress['progress_km'],
                progressTime: $progress['progress_time'],
                usageIntensity: $usage['usage_intensity'],
            ),
            [
                'progress_safe_end' => (float) $config->progress_safe_end,
                'progress_approaching_peak' => (float) $config->progress_approaching_peak,
                'progress_critical_full' => (float) $config->progress_critical_full,
                'usage_normal_full_until' => (float) $config->usage_normal_full_until,
                'usage_intensive_full_from' => (float) $config->usage_intensive_full_from,
            ],
            $ruleDefinitions,
        );
        $fuzzyStatus = RecommendationStatus::fromScore($fuzzyResult->score);

        $dueDates = $this->dueDateCalculator->calculate(
            kmSinceService: $progress['km_since_service'],
            intervalKm: $profile->interval_km,
            averageDailyKm: $usage['average_daily_km'],
            baselineServiceDate: $baselineServiceDate,
            intervalDays: $profile->interval_days,
            calculatedAt: $calculationDateTime,
        );
        $guard = $this->recommendationGuard->apply(
            $fuzzyStatus,
            $dueDates['estimated_due_date'],
            $calculationDateTime,
        );
        $window = $this->dueDateCalculator->recommendationWindow(
            $guard['final_status'],
            $dueDates['estimated_due_date'],
            $calculationDateTime,
        );

        $result = new RecommendationResult(
            currentOdometer: $latestOdometer->odometer,
            baselineOdometer: $vehicle->baseline_odometer,
            baselineServiceDate: $baselineServiceDate,
            intervalKm: $profile->interval_km,
            intervalDays: $profile->interval_days,
            kmSinceService: $progress['km_since_service'],
            daysSinceService: $progress['days_since_service'],
            averageDailyKm: $usage['average_daily_km'],
            baselineDailyUsage: $usage['baseline_daily_usage'],
            progressKm: $progress['progress_km'],
            progressTime: $progress['progress_time'],
            usageIntensity: $usage['usage_intensity'],
            usageFallbackApplied: $usage['fallback_applied'],
            fuzzyResult: $fuzzyResult,
            fuzzyStatus: $fuzzyStatus,
            estimatedDueByKm: $dueDates['estimated_due_by_km'],
            estimatedDueByTime: $dueDates['estimated_due_by_time'],
            estimatedDueDate: $dueDates['estimated_due_date'],
            recommendedFromDate: $window['recommended_from_date'],
            recommendedToDate: $window['recommended_to_date'],
            recommendedDate: $window['recommended_date'],
            finalStatus: $guard['final_status'],
            guardApplied: $guard['guard_applied'],
            guardReason: $guard['guard_reason'],
            calculatedAt: $calculationDateTime,
        );

        $calculation = $this->persist($vehicle, $config->id, $trigger, $result);

        if (
            $previousStatus !== null
            && in_array($result->finalStatus, [RecommendationStatus::Approaching, RecommendationStatus::Urgent], true)
            && $result->finalStatus->urgencyRank() > $previousStatus->urgencyRank()
        ) {
            RecommendationStatusChanged::dispatch($calculation, $previousStatus);
        }

        return $result;
    }

    private function persist(
        Vehicle $vehicle,
        int $fuzzyConfigId,
        CalculationTrigger $trigger,
        RecommendationResult $result,
    ): FuzzyCalculation {
        return DB::transaction(function () use ($vehicle, $fuzzyConfigId, $trigger, $result): FuzzyCalculation {
            $memberships = $result->fuzzyResult->memberships;
            $calculation = FuzzyCalculation::query()->create([
                'vehicle_id' => $vehicle->id,
                'fuzzy_config_id' => $fuzzyConfigId,
                'trigger_type' => $trigger,
                'interval_km_snapshot' => $result->intervalKm,
                'interval_days_snapshot' => $result->intervalDays,
                'current_odometer' => $result->currentOdometer,
                'baseline_odometer' => $result->baselineOdometer,
                'baseline_service_date' => $result->baselineServiceDate,
                'km_since_service' => $result->kmSinceService,
                'days_since_service' => $result->daysSinceService,
                'average_daily_km' => round($result->averageDailyKm, 2),
                'baseline_daily_usage' => round($result->baselineDailyUsage, 2),
                'progress_km' => round($result->progressKm, 2),
                'progress_time' => round($result->progressTime, 2),
                'usage_intensity' => round($result->usageIntensity, 2),
                'km_safe_mu' => round($memberships->kmSafe, 4),
                'km_approaching_mu' => round($memberships->kmApproaching, 4),
                'km_critical_mu' => round($memberships->kmCritical, 4),
                'time_safe_mu' => round($memberships->timeSafe, 4),
                'time_approaching_mu' => round($memberships->timeApproaching, 4),
                'time_critical_mu' => round($memberships->timeCritical, 4),
                'usage_normal_mu' => round($memberships->usageNormal, 4),
                'usage_intensive_mu' => round($memberships->usageIntensive, 4),
                'score' => round($result->fuzzyResult->score, 2),
                'fuzzy_status' => $result->fuzzyStatus,
                'estimated_due_by_km' => $result->estimatedDueByKm,
                'estimated_due_by_time' => $result->estimatedDueByTime,
                'estimated_due_date' => $result->estimatedDueDate,
                'recommended_from_date' => $result->recommendedFromDate,
                'recommended_to_date' => $result->recommendedToDate,
                'recommended_date' => $result->recommendedDate,
                'final_status' => $result->finalStatus,
                'guard_applied' => $result->guardApplied,
                'guard_reason' => $result->guardReason,
                'calculated_at' => $result->calculatedAt,
            ]);

            $calculation->ruleResults()->createMany(array_map(
                static function ($rule): array {
                    $alpha = round($rule->alpha, 4);
                    $zValue = round($rule->zValue, 4);

                    return [
                        'fuzzy_rule_id' => $rule->ruleId,
                        'alpha' => $alpha,
                        'z_value' => $zValue,
                        'weighted_value' => round($alpha * $zValue, 4),
                    ];
                },
                $result->fuzzyResult->activeRuleResults(),
            ));

            return $calculation;
        }, attempts: 3);
    }

    /** @param list<string> $codes */
    private function assertCanonicalRules(array $codes): void
    {
        $expected = array_map(
            static fn (int $number): string => sprintf('R%02d', $number),
            range(1, 18),
        );

        if ($codes !== $expected) {
            throw new InvalidFuzzyRuleSetException(
                'The fuzzy engine requires exactly the canonical R01-R18 rule set.',
            );
        }
    }
}
