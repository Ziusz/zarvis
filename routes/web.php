<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;
use App\Http\Controllers\Business\StaffController;
use App\Http\Controllers\Business\ServiceController;
use App\Http\Controllers\Business\VenueController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\AnalyticsController;
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
    // Main Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Business Owner Dashboard Routes
    Route::prefix('business')->middleware(['auth', 'business.owner'])->name('business.')->group(function () {
        // Dashboard Overview
        Route::get('/dashboard', [BusinessDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        
        // Staff Management
        Route::resource('staff', StaffController::class);
        Route::post('staff/{staff}/availability', [StaffController::class, 'updateAvailability'])
            ->name('staff.availability.update');
            
        // Service Management
        Route::resource('services', ServiceController::class);
        Route::post('services/{service}/availability', [ServiceController::class, 'updateAvailability'])
            ->name('services.availability.update');
            
        // Venue Management
        Route::resource('venues', VenueController::class);
        
        // Settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        
        // Bookings Management
        Route::get('bookings', [BusinessDashboardController::class, 'bookings'])->name('bookings.index');
        Route::get('bookings/{booking}', [BusinessDashboardController::class, 'showBooking'])->name('bookings.show');
        Route::put('bookings/{booking}/status', [BusinessDashboardController::class, 'updateBookingStatus'])
            ->name('bookings.status.update');
    });

    // Booking Routes
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/create/{business:slug}/{service:slug}', [BookingController::class, 'create'])->name('create');
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
