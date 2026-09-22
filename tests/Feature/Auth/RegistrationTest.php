<?php

use App\Enums\UserRole;
use App\Models\User;

it('shows the customer registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Mulai catat perawatan');
});

it('registers every public account as a customer', function () {
    $response = $this->withSession(['_token' => 'test-token'])->post(route('register'), [
        '_token' => 'test-token',
        'name' => 'Budi Santoso',
        'email' => 'budi@example.test',
        'phone' => '081234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => UserRole::Admin->value,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'budi@example.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->phone)->toBe('081234567890');
});

it('validates customer registration details', function () {
    $this->withSession(['_token' => 'test-token'])->post(route('register'), [
        '_token' => 'test-token',
        'name' => '',
        'email' => 'invalid',
        'phone' => 'not-a-phone',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
});
