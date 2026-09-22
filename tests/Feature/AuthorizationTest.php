<?php

use App\Models\User;

it('redirects guests to the Filament login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('forbids customers from the Filament admin panel', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/admin')
        ->assertForbidden();
});

it('allows admins into the Filament panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Bengkel Rutin');
});

it('keeps admins out of customer-only routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertForbidden();
});
