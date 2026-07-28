<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('role:admin_surat')->group(function () {
            Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
                ->name('users.status');
            Route::resource('users', UserController::class)->except('destroy');

            Route::patch('/divisions/{division}/status', [DivisionController::class, 'updateStatus'])
                ->name('divisions.status');
            Route::resource('divisions', DivisionController::class)->except('destroy');
        });
    });

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
