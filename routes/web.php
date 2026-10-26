<?php

use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/ebooks/{ebook}', [EbookController::class, 'show'])
    ->middleware(['auth', 'verified', 'throttle:30,1'])->name('ebooks.show');

foreach (array_keys(config('policies')) as $policy) {
    Route::view('/'.$policy, 'policies.show', ['policyKey' => $policy])->name($policy);
}

Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [AccountSecurityController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth.reset-password', ['token' => $token]))->name('password.reset');
    Route::post('/reset-password', [AccountSecurityController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [AccountSecurityController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AccountSecurityController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('/dashboard', DashboardController::class)->middleware('verified')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'verified'])->prefix('simulation')->group(function () {
    Route::get('/', [SimulationController::class, 'index'])->name('simulation');
    Route::post('/purchase', [SimulationController::class, 'purchase'])->middleware('throttle:10,1')->name('simulation.purchase');
    Route::post('/withdrawals', [SimulationController::class, 'withdraw'])->middleware('throttle:10,1')->name('simulation.withdraw');
    Route::get('/admin', [SimulationController::class, 'admin'])->name('simulation.admin');
    Route::post('/admin/withdrawals/{id}', [SimulationController::class, 'review'])->middleware('throttle:20,1')->name('simulation.review');
    Route::post('/admin/purchases/{id}/refund', [SimulationController::class, 'refund'])->middleware('throttle:20,1')->name('simulation.refund');
});
