<?php

use App\Models\User;

it('authenticates a customer and redirects to the dashboard', function () {
    $user = User::factory()->create();

    $this->withSession(['_token' => 'test-token'])->post(route('login'), [
        '_token' => 'test-token',
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->withSession(['_token' => 'test-token'])->post(route('login'), [
        '_token' => 'test-token',
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs an authenticated user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('logout'), ['_token' => 'test-token'])
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
