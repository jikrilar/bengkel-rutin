<?php

use App\Livewire\Dashboard\Overview;
use App\Models\User;
use Livewire\Livewire;

it('renders the polished public landing page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Rawat kendaraan.')
        ->assertSee('Cara kerja')
        ->assertSee('workshop-service-hero.png');
});

it('renders every customer shell destination with complete navigation', function () {
    $customer = User::factory()->create();

    foreach ([
        'dashboard',
        'vehicles.index',
        'recommendations.index',
        'bookings.index',
        'service-history.index',
        'notifications.index',
        'profile.edit',
    ] as $routeName) {
        $this->actingAs($customer)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Kendaraan')
            ->assertSee('Rekomendasi Servis')
            ->assertSee('Booking')
            ->assertSee('Riwayat Servis')
            ->assertSee('Notifikasi')
            ->assertSee('Profil');
    }
});

it('renders the dashboard through Livewire', function () {
    $customer = User::factory()->create(['name' => 'Sari Wulandari']);

    Livewire::actingAs($customer)
        ->test(Overview::class)
        ->assertSee('Halo, Sari')
        ->assertSee('Belum ada kendaraan')
        ->assertSee('Tambah Kendaraan');
});

it('redirects guests away from customer pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
