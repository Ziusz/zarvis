<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BookingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', WelcomeController::class)->name('welcome');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Booking Routes
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/create/{business}/{service}', [BookingController::class, 'create'])->name('create');
        Route::post('/time-slots', [BookingController::class, 'getTimeSlots'])->name('time-slots');
        Route::post('/staff', [BookingController::class, 'getAvailableStaff'])->name('staff');
        Route::post('/store', [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::delete('/{booking}', [BookingController::class, 'cancel'])->name('cancel');
        Route::patch('/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('reschedule');
    });
});

// Category Routes
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])
    ->name('categories.show');

// Business Routes
Route::get('/businesses/register', [BusinessController::class, 'create'])
    ->name('businesses.register');

Route::get('/businesses/{business:slug}', [BusinessController::class, 'show'])
    ->name('businesses.show');
