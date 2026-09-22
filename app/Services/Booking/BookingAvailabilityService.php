<?php

namespace App\Services\Booking;

use App\DTOs\Booking\BookingSlot;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\WorkshopSetting;
use App\Services\Workshop\WorkshopScheduleService;
use Carbon\CarbonImmutable;
use DateTimeInterface;

class BookingAvailabilityService
{
    public function __construct(private readonly WorkshopScheduleService $scheduleService) {}

    /** @return list<BookingSlot> */
    public function slotsForDate(
        DateTimeInterface|string $date,
        ?int $excludeBookingId = null,
        ?WorkshopSetting $settings = null,
    ): array {
        $settings ??= $this->scheduleService->settings();
        $generatedSlots = $this->scheduleService->generateTimeSlots($date, $settings);

        if ($generatedSlots === []) {
            return [];
        }

        $timezone = $settings->timezone;
        $day = $generatedSlots[0]['starts_at']->startOfDay();
        $bookings = Booking::query()
            ->whereIn('status', BookingStatus::activeValues())
            ->where('scheduled_at', '>=', $day)
            ->where('scheduled_at', '<', $day->addDay())
            ->when($excludeBookingId, fn ($query) => $query->whereKeyNot($excludeBookingId))
            ->get(['id', 'scheduled_at', 'duration_minutes']);
        $now = CarbonImmutable::now($timezone);

        return array_map(function (array $slot) use ($bookings, $settings, $timezone, $now): BookingSlot {
            $usedCapacity = $bookings->filter(function (Booking $booking) use ($slot, $timezone): bool {
                $bookingStart = CarbonImmutable::instance($booking->scheduled_at)->setTimezone($timezone);
                $bookingEnd = $bookingStart->addMinutes($booking->duration_minutes);

                return $bookingStart->lt($slot['ends_at']) && $bookingEnd->gt($slot['starts_at']);
            })->count();
            $remaining = max(0, $settings->slot_capacity - $usedCapacity);

            if ($slot['starts_at']->lte($now)) {
                return new BookingSlot(
                    $slot['starts_at'],
                    $slot['ends_at'],
                    $settings->slot_capacity,
                    0,
                    false,
                    'closed',
                    'Waktu slot sudah berlalu.',
                );
            }

            if ($remaining === 0) {
                return new BookingSlot(
                    $slot['starts_at'],
                    $slot['ends_at'],
                    $settings->slot_capacity,
                    0,
                    false,
                    'full',
                    'Kapasitas slot sudah penuh.',
                );
            }

            return new BookingSlot(
                $slot['starts_at'],
                $slot['ends_at'],
                $settings->slot_capacity,
                $remaining,
                true,
                $remaining < $settings->slot_capacity ? 'limited' : 'available',
            );
        }, $generatedSlots);
    }

    public function check(
        DateTimeInterface|string $scheduledAt,
        ?int $excludeBookingId = null,
        ?WorkshopSetting $settings = null,
    ): BookingSlot {
        $settings ??= $this->scheduleService->settings();
        $requested = $scheduledAt instanceof DateTimeInterface
            ? CarbonImmutable::instance($scheduledAt)->setTimezone($settings->timezone)
            : CarbonImmutable::parse($scheduledAt, $settings->timezone);

        $slot = collect($this->slotsForDate($requested, $excludeBookingId, $settings))
            ->first(fn (BookingSlot $slot): bool => $slot->startsAt->equalTo($requested));

        if ($slot instanceof BookingSlot) {
            return $slot;
        }

        $schedule = $this->scheduleService->resolve($requested, $settings);

        return new BookingSlot(
            $requested,
            $requested->addMinutes($settings->slot_duration_minutes),
            $settings->slot_capacity,
            0,
            false,
            'closed',
            $schedule->reason ?: 'Waktu yang dipilih bukan slot operasional.',
        );
    }
}
