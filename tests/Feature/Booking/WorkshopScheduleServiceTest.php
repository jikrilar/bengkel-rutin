<?php

use App\Models\OperatingHour;
use App\Models\ScheduleException;
use App\Models\WorkshopSetting;
use App\Services\Workshop\WorkshopScheduleService;
use Carbon\CarbonImmutable;
use Database\Seeders\OperatingHourSeeder;
use Database\Seeders\WorkshopSettingSeeder;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-22 07:00:00');
    $this->seed([WorkshopSettingSeeder::class, OperatingHourSeeder::class]);
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('resolves an open operating day', function () {
    $schedule = app(WorkshopScheduleService::class)->resolve('2026-09-22');

    expect($schedule->isOpen)->toBeTrue()
        ->and($schedule->opensAt->format('H:i'))->toBe('08:00')
        ->and($schedule->closesAt->format('H:i'))->toBe('17:00')
        ->and($schedule->exceptionApplied)->toBeFalse();
});

it('resolves a regular closed day', function () {
    expect(OperatingHour::query()->where('day_of_week', 7)->sole()->is_open)->toBeFalse();

    $schedule = app(WorkshopScheduleService::class)->resolve('2026-09-27');

    expect($schedule->isOpen)->toBeFalse()
        ->and($schedule->reason)->toContain('tutup');
});

it('applies a fully closed schedule exception', function () {
    $settings = WorkshopSetting::query()->sole();
    ScheduleException::query()->create([
        'workshop_setting_id' => $settings->id,
        'date' => '2026-09-23',
        'is_closed' => true,
        'reason' => 'Libur inventaris.',
    ]);

    $schedule = app(WorkshopScheduleService::class)->resolve('2026-09-23');

    expect($schedule->isOpen)->toBeFalse()
        ->and($schedule->exceptionApplied)->toBeTrue()
        ->and($schedule->reason)->toBe('Libur inventaris.');
});

it('applies custom exception hours and generates dynamic slots', function () {
    $settings = WorkshopSetting::query()->sole();
    ScheduleException::query()->create([
        'workshop_setting_id' => $settings->id,
        'date' => '2026-09-23',
        'is_closed' => false,
        'open_time' => '08:00:00',
        'close_time' => '12:00:00',
        'reason' => 'Jam operasional khusus.',
    ]);

    $service = app(WorkshopScheduleService::class);
    $schedule = $service->resolve('2026-09-23');
    $slots = $service->generateTimeSlots('2026-09-23');

    expect($schedule->isOpen)->toBeTrue()
        ->and($schedule->exceptionApplied)->toBeTrue()
        ->and($schedule->closesAt->format('H:i'))->toBe('12:00')
        ->and($slots)->toHaveCount(4)
        ->and($slots[0]['starts_at']->format('H:i'))->toBe('08:00')
        ->and($slots[3]['starts_at']->format('H:i'))->toBe('11:00');
});

it('generates slots from duration without a booking slots table', function () {
    $settings = WorkshopSetting::query()->sole();
    $settings->update(['slot_duration_minutes' => 30]);

    $slots = app(WorkshopScheduleService::class)->generateTimeSlots('2026-09-23');

    expect($slots)->toHaveCount(18)
        ->and($slots[0]['starts_at']->format('H:i'))->toBe('08:00')
        ->and($slots[17]['ends_at']->format('H:i'))->toBe('17:00');
});
