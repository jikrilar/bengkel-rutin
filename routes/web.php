<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\FuzzyCalculationController;
use App\Http\Controllers\Customer\OdometerController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\RecommendationController;
use App\Http\Controllers\Customer\ServiceHistoryController;
use App\Http\Controllers\Customer\VehicleController;
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

    Route::resource('vehicles', VehicleController::class)->except(['destroy']);
    Route::post('/vehicles/{vehicle}/odometer', [OdometerController::class, 'store'])
        ->name('vehicles.odometer.store');

    Route::get('/recommendations', [RecommendationController::class, 'index'])
        ->name('recommendations.index');
    Route::get('/recommendations/{vehicle}', [RecommendationController::class, 'show'])
        ->name('recommendations.show');
    Route::get('/recommendations/{vehicle}/calculation/{calculation}', [FuzzyCalculationController::class, 'show'])
        ->name('recommendations.calculation.show');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/service-history', [ServiceHistoryController::class, 'index'])
        ->name('service-history.index');
    Route::get('/service-history/{record}', [ServiceHistoryController::class, 'show'])
        ->name('service-history.show');

    Route::view('/notifications', 'customer.placeholder', [
        'eyebrow' => 'Tetap Terinformasi',
        'title' => 'Notifikasi',
        'description' => 'Pengingat servis dan pembaruan booking akan hadir di sini.',
        'emptyTitle' => 'Tidak ada notifikasi baru',
        'emptyDescription' => 'Kami akan memberi kabar ketika ada hal yang perlu Anda tindak lanjuti.',
        'icon' => 'heroicon-o-bell',
    ])->name('notifications.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
