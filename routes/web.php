<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'active'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('role:admin_surat')->group(function () {
            Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
                ->name('users.status');
            Route::resource('users', UserController::class)->except('destroy');
        });
    });

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
