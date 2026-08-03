<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\IncomingLetterController;
use App\Http\Controllers\IncomingLetterReviewController;
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

        Route::get('/incoming-letters', [IncomingLetterController::class, 'index'])
            ->name('incoming-letters.index');
        Route::get('/incoming-letters/{incomingLetter}/preview', [IncomingLetterController::class, 'preview'])
            ->name('incoming-letters.preview');
        Route::get('/incoming-letters/{incomingLetter}/download', [IncomingLetterController::class, 'download'])
            ->name('incoming-letters.download');
        Route::get('/incoming-letters/{incomingLetter}/review', [IncomingLetterReviewController::class, 'create'])
            ->name('incoming-letters.review.create');
        Route::post('/incoming-letters/{incomingLetter}/review', [IncomingLetterReviewController::class, 'store'])
            ->name('incoming-letters.review.store');

        Route::middleware('role:admin_surat')->group(function () {
            Route::get('/incoming-letters/create', [IncomingLetterController::class, 'create'])
                ->name('incoming-letters.create');
            Route::post('/incoming-letters', [IncomingLetterController::class, 'store'])
                ->name('incoming-letters.store');
            Route::get('/incoming-letters/{incomingLetter}/edit', [IncomingLetterController::class, 'edit'])
                ->name('incoming-letters.edit');
            Route::match(['put', 'patch'], '/incoming-letters/{incomingLetter}', [IncomingLetterController::class, 'update'])
                ->name('incoming-letters.update');
            Route::patch('/incoming-letters/{incomingLetter}/submit-for-review', [IncomingLetterController::class, 'submitForReview'])
                ->name('incoming-letters.submit-for-review');

            Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
                ->name('users.status');
            Route::resource('users', UserController::class)->except('destroy');

            Route::patch('/divisions/{division}/status', [DivisionController::class, 'updateStatus'])
                ->name('divisions.status');
            Route::resource('divisions', DivisionController::class)->except('destroy');
        });

        Route::get('/incoming-letters/{incomingLetter}', [IncomingLetterController::class, 'show'])
            ->name('incoming-letters.show');
    });

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
