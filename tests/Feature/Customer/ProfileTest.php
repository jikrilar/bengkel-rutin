<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withSession(['_token' => 'test-token']);
});

it('updates the authenticated customer profile', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->patch(route('profile.update'), [
        '_token' => 'test-token',
        'name' => 'Ratna Puspita',
        'email' => 'ratna@example.test',
        'phone' => '081234567890',
    ])->assertRedirect()->assertSessionHas('success');

    expect($customer->fresh()->name)->toBe('Ratna Puspita')
        ->and($customer->fresh()->email)->toBe('ratna@example.test')
        ->and($customer->fresh()->phone)->toBe('081234567890');
});

it('updates password only when the current password is correct', function () {
    $customer = User::factory()->create(['password' => 'password']);

    $this->actingAs($customer)->put(route('profile.password.update'), [
        '_token' => 'test-token',
        'current_password' => 'password',
        'password' => 'password-baru-aman',
        'password_confirmation' => 'password-baru-aman',
    ])->assertRedirect()->assertSessionHas('password_success');

    expect(Hash::check('password-baru-aman', $customer->fresh()->password))->toBeTrue();

    $this->actingAs($customer)->put(route('profile.password.update'), [
        '_token' => 'test-token',
        'current_password' => 'salah',
        'password' => 'password-lain-aman',
        'password_confirmation' => 'password-lain-aman',
    ])->assertSessionHasErrors('current_password');
});
