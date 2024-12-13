<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the business owner's dashboard.
     */
    public function index(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();
        
        // Get today's bookings
        $today = Carbon::today();
        $todayBookings = $business->bookings()
            ->whereDate('start_time', $today)
            ->with(['service', 'staff', 'user'])
            ->orderBy('start_time')
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'service' => [
                    'name' => $booking->service->name,
                    'duration' => $booking->service->duration,
                ],
                'staff' => $booking->staff ? [
                    'name' => $booking->staff->name,
                    'avatar' => $booking->staff->profile_photo_url,
                ] : null,
                'customer' => [
                    'name' => $booking->user->name,
                    'avatar' => $booking->user->profile_photo_url,
                ],
            ]);

        // Get quick stats
        $stats = [
            'total_bookings' => $business->bookings()->count(),
            'today_bookings' => $todayBookings->count(),
            'active_staff' => $business->staffMembers()->count(),
            'total_services' => $business->services()->count(),
            'total_revenue' => $business->bookings()
                ->where('payment_status', 'paid')
                ->sum('total_price'),
            'this_month_revenue' => $business->bookings()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total_price'),
        ];

        // Get recent activity
        $recentActivity = $business->bookings()
            ->with(['service', 'staff', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'type' => 'booking',
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'service' => $booking->service->name,
                'customer' => $booking->user->name,
                'staff' => $booking->staff?->name,
                'time' => $booking->created_at,
            ]);

        return Inertia::render('Business/Dashboard', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'logo' => $business->logo,
            ],
            'stats' => $stats,
            'todayBookings' => $todayBookings,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Display the business bookings.
     */
    public function bookings(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();
        
        $bookings = $business->bookings()
            ->with(['service', 'staff', 'user'])
            ->orderByDesc('start_time')
            ->paginate(20)
            ->through(fn ($booking) => [
                'id' => $booking->id,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'service' => [
                    'name' => $booking->service->name,
                    'duration' => $booking->service->duration,
                ],
                'staff' => $booking->staff ? [
                    'name' => $booking->staff->name,
                    'avatar' => $booking->staff->profile_photo_url,
                ] : null,
                'customer' => [
                    'name' => $booking->user->name,
                    'avatar' => $booking->user->profile_photo_url,
                ],
            ]);

        return Inertia::render('Business/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display a specific booking.
     */
    public function showBooking(Request $request, Booking $booking)
    {
        // Ensure the booking belongs to the business
        $business = $request->user()->businesses()->firstOrFail();
        if ($booking->business_id !== $business->id) {
            abort(404);
        }

        $booking->load(['service', 'staff', 'user', 'venue']);

        return Inertia::render('Business/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'service' => [
                    'name' => $booking->service->name,
                    'duration' => $booking->service->duration,
                    'price' => $booking->service->price,
                ],
                'staff' => $booking->staff ? [
                    'name' => $booking->staff->name,
                    'avatar' => $booking->staff->profile_photo_url,
                ] : null,
                'customer' => [
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'avatar' => $booking->user->profile_photo_url,
                ],
                'venue' => [
                    'name' => $booking->venue->name,
                    'address' => $booking->venue->address,
                ],
                'notes' => $booking->notes,
            ],
        ]);
    }

    /**
     * Update a booking's status.
     */
    public function updateBookingStatus(Request $request, Booking $booking)
    {
        // Ensure the booking belongs to the business
        $business = $request->user()->businesses()->firstOrFail();
        if ($booking->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $booking->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Booking status updated successfully.');
    }
} 