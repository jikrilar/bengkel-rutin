<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'customer'])->group(function (): void {
    Route::view('/dashboard', 'customer.dashboard')->name('dashboard');

    Route::view('/vehicles', 'customer.placeholder', [
        'eyebrow' => 'Garasi Anda',
        'title' => 'Kendaraan',
        'description' => 'Data kendaraan dan interval servis akan tersedia pada tahap berikutnya.',
        'emptyTitle' => 'Belum ada kendaraan',
        'emptyDescription' => 'Fitur penambahan kendaraan disiapkan pada T03 dan pengalaman lengkapnya pada task customer.',
        'icon' => 'heroicon-o-truck',
    ])->name('vehicles.index');

    Route::view('/recommendations', 'customer.placeholder', [
        'eyebrow' => 'Perawatan Terencana',
        'title' => 'Rekomendasi Servis',
        'description' => 'Rekomendasi berdasarkan jarak tempuh dan waktu akan dirangkum di sini.',
        'emptyTitle' => 'Belum ada rekomendasi',
        'emptyDescription' => 'Tambahkan kendaraan dan catatan odometer setelah fondasi domain tersedia.',
        'icon' => 'heroicon-o-wrench-screwdriver',
    ])->name('recommendations.index');

    Route::view('/bookings', 'customer.placeholder', [
        'eyebrow' => 'Kunjungan Bengkel',
        'title' => 'Booking',
        'description' => 'Jadwal aktif dan riwayat booking akan tersusun dalam satu alur.',
        'emptyTitle' => 'Belum ada booking',
        'emptyDescription' => 'Pemilihan slot bengkel akan tersedia pada tahap booking.',
        'icon' => 'heroicon-o-calendar-days',
    ])->name('bookings.index');

    Route::view('/service-history', 'customer.placeholder', [
        'eyebrow' => 'Catatan Kendaraan',
        'title' => 'Riwayat Servis',
        'description' => 'Semua pekerjaan servis akan tersimpan sebagai catatan yang mudah ditelusuri.',
        'emptyTitle' => 'Belum ada riwayat servis',
        'emptyDescription' => 'Riwayat akan muncul setelah kunjungan servis diselesaikan.',
        'icon' => 'heroicon-o-clipboard-document-list',
    ])->name('service-history.index');

    Route::view('/notifications', 'customer.placeholder', [
        'eyebrow' => 'Tetap Terinformasi',
        'title' => 'Notifikasi',
        'description' => 'Pengingat servis dan pembaruan booking akan hadir di sini.',
        'emptyTitle' => 'Tidak ada notifikasi baru',
        'emptyDescription' => 'Kami akan memberi kabar ketika ada hal yang perlu Anda tindak lanjuti.',
        'icon' => 'heroicon-o-bell',
    ])->name('notifications.index');

    Route::view('/profile', 'customer.placeholder', [
        'eyebrow' => 'Akun Customer',
        'title' => 'Profil',
        'description' => 'Kelola informasi kontak dan keamanan akun Anda.',
        'emptyTitle' => 'Pengaturan profil segera tersedia',
        'emptyDescription' => 'Data akun inti sudah tersimpan dan aman. Form pengelolaan profil hadir pada task customer.',
        'icon' => 'heroicon-o-user-circle',
    ])->name('profile.edit');
});
