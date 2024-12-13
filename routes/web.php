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
use App\Http\Middleware\BusinessOwner;
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
    // Business Owner Dashboard Routes
    Route::prefix('business')
        ->middleware('auth')
        ->middleware(BusinessOwner::class)
        ->name('business.')
        ->group(function () {
            // Dashboard Overview
            Route::get('/dashboard', [BusinessDashboardController::class, 'index'])->name('dashboard');
            Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
            
            // Staff Management
            Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
            Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
            Route::post('/staff/{staff}/availability', [StaffController::class, 'updateAvailability'])
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

    // Main Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Business routes
    Route::middleware('App\Http\Middleware\BusinessOwner')->group(function () {
        // Settings routes
        Route::get('/business/settings', [App\Http\Controllers\Business\SettingController::class, 'edit'])->name('business.settings');
        Route::get('/business/settings/profile', [App\Http\Controllers\Business\SettingController::class, 'profile'])->name('business.settings.profile');
        Route::get('/business/settings/hours', [App\Http\Controllers\Business\SettingController::class, 'hours'])->name('business.settings.hours');
        Route::get('/business/settings/services', [App\Http\Controllers\Business\SettingController::class, 'services'])->name('business.settings.services');
        Route::get('/business/settings/staff', [App\Http\Controllers\Business\SettingController::class, 'staff'])->name('business.settings.staff');

        // Update routes
        Route::put('/business/settings/profile', [App\Http\Controllers\Business\SettingController::class, 'updateProfile'])->name('business.settings.profile.update');
        Route::put('/business/settings/hours', [App\Http\Controllers\Business\SettingController::class, 'updateHours'])->name('business.settings.hours.update');

        // Service routes
        Route::post('/business/services', [App\Http\Controllers\Business\ServiceController::class, 'store'])->name('business.services.store');
        Route::put('/business/services/{service}', [App\Http\Controllers\Business\ServiceController::class, 'update'])->name('business.services.update');
        Route::delete('/business/services/{service}', [App\Http\Controllers\Business\ServiceController::class, 'destroy'])->name('business.services.destroy');
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
        Route::post('/available-dates', [BookingController::class, 'getAvailableDates'])->name('available-dates');
    });
});

// Category Routes
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])
    ->name('categories.show');

// Business Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/businesses/register', [BusinessController::class, 'create'])
        ->name('businesses.register');
        
    Route::post('/businesses', [BusinessController::class, 'store'])
        ->name('businesses.store');
});

// Public Business Routes
Route::get('/businesses/{business:slug}', [BusinessController::class, 'show'])
    ->name('businesses.show');
