<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('sends a password reset notification', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->withSession(['_token' => 'test-token'])->post(route('password.email'), [
        '_token' => 'test-token',
        'email' => $user->email,
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets a password with a valid token', function () {
    Notification::fake();
    $user = User::factory()->create();
    $token = null;

    $this->withSession(['_token' => 'test-token'])->post(route('password.email'), [
        '_token' => 'test-token',
        'email' => $user->email,
    ]);

    Notification::assertSentTo(
        $user,
        ResetPassword::class,
        function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        },
    );

    $this->withSession(['_token' => 'test-token'])->post(route('password.store'), [
        '_token' => 'test-token',
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('login'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
