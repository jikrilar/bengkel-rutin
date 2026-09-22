<?php

namespace App\Services\Workshop;

use App\DTOs\Booking\ResolvedSchedule;
use App\Models\WorkshopSetting;
use Carbon\CarbonImmutable;
use DateTimeInterface;

class WorkshopScheduleService
{
    public function settings(): WorkshopSetting
    {
        return WorkshopSetting::query()->orderBy('id')->firstOrFail();
    }

    public function resolve(
        DateTimeInterface|string $date,
        ?WorkshopSetting $settings = null,
    ): ResolvedSchedule {
        $settings ??= $this->settings();
        $localDate = $this->localDate($date, $settings->timezone);
        $dateString = $localDate->toDateString();

        $exception = $settings->scheduleExceptions()
            ->whereDate('date', $dateString)
            ->first();

        if ($exception !== null) {
            if ($exception->is_closed) {
                return new ResolvedSchedule(
                    date: $localDate,
                    isOpen: false,
                    opensAt: null,
                    closesAt: null,
                    exceptionApplied: true,
                    reason: $exception->reason ?: 'Bengkel tutup pada tanggal ini.',
                );
            }

            return $this->openSchedule(
                $localDate,
                (string) $exception->open_time,
                (string) $exception->close_time,
                true,
                $exception->reason,
                $settings->timezone,
            );
        }

        $hours = $settings->operatingHours()
            ->where('day_of_week', $localDate->isoWeekday())
            ->first();

        if ($hours === null || ! $hours->is_open) {
            return new ResolvedSchedule(
                date: $localDate,
                isOpen: false,
                opensAt: null,
                closesAt: null,
                exceptionApplied: false,
                reason: 'Bengkel tutup pada hari ini.',
            );
        }

        return $this->openSchedule(
            $localDate,
            (string) $hours->open_time,
            (string) $hours->close_time,
            false,
            null,
            $settings->timezone,
        );
    }

    /** @return list<array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}> */
    public function generateTimeSlots(
        DateTimeInterface|string $date,
        ?WorkshopSetting $settings = null,
    ): array {
        $settings ??= $this->settings();
        $schedule = $this->resolve($date, $settings);

        if (! $schedule->isOpen || $schedule->opensAt === null || $schedule->closesAt === null) {
            return [];
        }

        $slots = [];
        $cursor = $schedule->opensAt;

        while ($cursor->addMinutes($settings->slot_duration_minutes)->lte($schedule->closesAt)) {
            $endsAt = $cursor->addMinutes($settings->slot_duration_minutes);
            $slots[] = [
                'starts_at' => $cursor,
                'ends_at' => $endsAt,
            ];
            $cursor = $endsAt;
        }

        return $slots;
    }

    private function openSchedule(
        CarbonImmutable $date,
        string $openTime,
        string $closeTime,
        bool $exceptionApplied,
        ?string $reason,
        string $timezone,
    ): ResolvedSchedule {
        if ($openTime === '' || $closeTime === '') {
            return new ResolvedSchedule($date, false, null, null, $exceptionApplied, $reason);
        }

        $opensAt = CarbonImmutable::parse($date->toDateString().' '.$openTime, $timezone);
        $closesAt = CarbonImmutable::parse($date->toDateString().' '.$closeTime, $timezone);

        if ($closesAt->lte($opensAt)) {
            return new ResolvedSchedule(
                $date,
                false,
                null,
                null,
                $exceptionApplied,
                'Jam operasional tidak valid.',
            );
        }

        return new ResolvedSchedule(
            $date,
            true,
            $opensAt,
            $closesAt,
            $exceptionApplied,
            $reason,
        );
    }

    private function localDate(DateTimeInterface|string $date, string $timezone): CarbonImmutable
    {
        return $date instanceof DateTimeInterface
            ? CarbonImmutable::instance($date)->setTimezone($timezone)->startOfDay()
            : CarbonImmutable::parse($date, $timezone)->startOfDay();
    }
}
