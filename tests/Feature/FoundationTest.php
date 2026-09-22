<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;

it('casts the persisted user role enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

it('registers scheduler maintenance tasks', function () {
    $events = collect(Schedule::events())->map(fn ($event) => $event->command)->filter()->implode(' ');

    expect($events)
        ->toContain('queue:prune-batches')
        ->toContain('queue:prune-failed');
});
