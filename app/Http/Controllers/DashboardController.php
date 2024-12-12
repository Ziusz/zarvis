<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $business = $user->ownedBusinesses()->first();

        // If user is a business owner
        if ($business) {
            $today = Carbon::today();

            // Get today's bookings
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

            return Inertia::render('Dashboard', [
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

        // If user is a client
        $upcomingBookings = $user->bookings()
            ->with(['business', 'service', 'staff', 'venue'])
            ->whereDate('start_time', '>=', now())
            ->orderBy('start_time')
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'can_be_cancelled' => $booking->canBeCancelled(),
                'business' => [
                    'name' => $booking->business->name,
                    'logo' => $booking->business->logo,
                ],
                'service' => [
                    'name' => $booking->service->name,
                    'duration' => $booking->service->duration,
                ],
                'staff' => $booking->staff ? [
                    'name' => $booking->staff->name,
                    'avatar' => $booking->staff->profile_photo_url,
                ] : null,
            ]);

        $pastBookings = $user->bookings()
            ->whereDate('start_time', '<', now())
            ->orderByDesc('start_time')
            ->get();

        return Inertia::render('Dashboard', [
            'upcomingBookings' => $upcomingBookings,
            'pastBookings' => $pastBookings,
        ]);
    }
} 