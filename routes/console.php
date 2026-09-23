<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:prune-batches --hours=48')
    ->dailyAt('01:15')
    ->withoutOverlapping();

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('01:30')
    ->withoutOverlapping();

Schedule::command('recommendations:recalculate-daily')
    ->dailyAt('00:15')
    ->withoutOverlapping(120);

Schedule::command('recommendations:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping(60);

Schedule::command('bookings:send-upcoming-reminders')
    ->dailyAt('08:15')
    ->withoutOverlapping(60);
