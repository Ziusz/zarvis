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

            // Calendar Management
            Route::get('calendar', [App\Http\Controllers\Business\AppointmentController::class, 'calendar'])
                ->name('calendar');
            Route::get('appointments', [App\Http\Controllers\Business\AppointmentController::class, 'index'])
                ->name('appointments.index');
            Route::post('appointments', [App\Http\Controllers\Business\AppointmentController::class, 'store'])
                ->name('appointments.store');
            Route::put('appointments/{appointment}/move', [App\Http\Controllers\Business\AppointmentController::class, 'move'])
                ->name('appointments.move');
            Route::post('appointments/{appointment}/confirm', [App\Http\Controllers\Business\AppointmentController::class, 'confirm'])
                ->name('appointments.confirm');
            Route::post('appointments/{appointment}/cancel', [App\Http\Controllers\Business\AppointmentController::class, 'cancel'])
                ->name('appointments.cancel');
            Route::get('customers/search', [App\Http\Controllers\Business\AppointmentController::class, 'searchCustomers'])
                ->name('customers.search');

            // Staff Availability Management
            Route::prefix('staff/availability')->name('staff.availability.')->group(function () {
                Route::get('/', [App\Http\Controllers\Business\StaffAvailabilityController::class, 'index'])
                    ->name('index');
                Route::get('/{staff}', [App\Http\Controllers\Business\StaffAvailabilityController::class, 'show'])
                    ->name('show');
                Route::post('/{staff}/sync', [App\Http\Controllers\Business\StaffAvailabilityController::class, 'sync'])
                    ->name('sync');
                Route::put('/{staff}', [App\Http\Controllers\Business\StaffAvailabilityController::class, 'update'])
                    ->name('update');
                Route::post('/{staff}/bulk', [App\Http\Controllers\Business\StaffAvailabilityController::class, 'bulkUpdate'])
                    ->name('bulk-update');
            });
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
        Route::get('/available-dates', [BookingController::class, 'getAvailableDates'])->name('available-dates');
        Route::get('/time-slots', [BookingController::class, 'getTimeSlots'])->name('time-slots');
        Route::post('/staff', [BookingController::class, 'getAvailableStaff'])->name('staff');
        Route::get('/create/{business:slug}/{service:slug}', [BookingController::class, 'create'])->name('create');
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
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/businesses/register', [BusinessController::class, 'create'])
        ->name('businesses.register');
        
    Route::post('/businesses', [BusinessController::class, 'store'])
        ->name('businesses.store');
});

// Public Business Routes
Route::get('/businesses/{business:slug}', [BusinessController::class, 'show'])
    ->name('businesses.show');
