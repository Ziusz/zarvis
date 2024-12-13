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
        
        // If user has a business, redirect to business dashboard
        if ($user->businesses()->exists()) {
            return redirect()->route('business.dashboard');
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