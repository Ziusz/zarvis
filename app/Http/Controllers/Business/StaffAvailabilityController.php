<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use App\Models\StaffAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffAvailabilityController extends Controller
{
    /**
     * Show staff availability management page
     */
    public function index()
    {
        $business = auth()->user()->businesses()->firstOrFail();
        
        $staff = $business->staffMembers()
            ->with(['staffAvailabilities' => function ($query) {
                $query->where('date', '>=', now()->startOfWeek())
                      ->where('date', '<=', now()->endOfWeek());
            }])
            ->get()
            ->map(fn ($staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'role' => $staff->pivot->role,
                'availabilities' => $staff->staffAvailabilities
                    ->groupBy('date')
                    ->map(fn ($dayAvailabilities) => $dayAvailabilities->map(fn ($availability) => [
                        'id' => $availability->id,
                        'date' => $availability->date,
                        'start_time' => $availability->start_time,
                        'end_time' => $availability->end_time,
                        'is_available' => $availability->is_available,
                        'status' => $availability->status,
                    ])->values())
            ]);

        return Inertia::render('Business/Staff/Availability', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'opening_hours' => $business->opening_hours,
            ],
            'staff' => $staff,
        ]);
    }

    /**
     * Show individual staff availability management page
     */
    public function show(User $staff)
    {
        $business = auth()->user()->businesses()->firstOrFail();

        // Check if staff belongs to business
        if (!$business->staffMembers()->where('users.id', $staff->id)->exists()) {
            abort(404);
        }

        $staffMember = $business->staffMembers()
            ->with(['staffAvailabilities' => function ($query) use ($business) {
                $query->where('business_id', $business->id)
                      ->where('date', '>=', now()->startOfWeek())
                      ->where('date', '<=', now()->endOfWeek());
            }])
            ->where('users.id', $staff->id)
            ->first();

        $mappedStaff = [
            'id' => $staffMember->id,
            'name' => $staffMember->name,
            'role' => $staffMember->pivot->role,
            'availabilities' => $staffMember->staffAvailabilities
                ->groupBy('date')
                ->map(fn ($dayAvailabilities) => $dayAvailabilities->map(fn ($availability) => [
                    'id' => $availability->id,
                    'date' => $availability->date->format('Y-m-d'),
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                    'is_available' => $availability->is_available,
                    'status' => $availability->status,
                ])->values())
        ];

        return Inertia::render('Business/Staff/Availability', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'opening_hours' => $business->opening_hours,
            ],
            'staff' => [$mappedStaff], // Wrap in array to reuse the same component
            'singleStaffMode' => true,
        ]);
    }

    /**
     * Sync staff availability with business hours
     */
    public function sync(Request $request, User $staff)
    {
        $business = auth()->user()->businesses()->firstOrFail();

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get opening hours
        $opening_hours = $business->opening_hours;
        if (is_string($opening_hours)) {
            $opening_hours = json_decode($opening_hours, true);
        }

        // Loop through each day
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = strtolower($date->format('l'));
            $dayHours = $opening_hours[$dayOfWeek] ?? null;

            // Check if business is open
            $isOpen = false;
            $startTime = null;
            $endTime = null;

            if ($dayHours) {
                if (isset($dayHours['is_open'])) {
                    // New format
                    $isOpen = $dayHours['is_open'];
                    $startTime = $dayHours['start'];
                    $endTime = $dayHours['end'];
                } else {
                    // Old format
                    $isOpen = !($dayHours['closed'] ?? true);
                    $startTime = $dayHours['open'] ?? null;
                    $endTime = $dayHours['close'] ?? null;
                }
            }

            if ($isOpen && $startTime && $endTime) {
                StaffAvailability::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'user_id' => $staff->id,
                        'venue_id' => $business->primaryVenue->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_available' => true,
                        'status' => 'available',
                    ]
                );
            }
        }

        return back()->with('success', 'Staff availability synced with business hours.');
    }

    /**
     * Update staff availability for a specific date
     */
    public function update(Request $request, User $staff)
    {
        $business = auth()->user()->businesses()->firstOrFail();

        $request->validate([
            'date' => 'required|date',
            'availabilities' => 'required|array',
            'availabilities.*.start_time' => 'required|date_format:H:i',
            'availabilities.*.end_time' => 'required|date_format:H:i|after:availabilities.*.start_time',
            'availabilities.*.status' => 'required|in:available,unavailable,on-leave',
        ]);

        // Delete existing availabilities for this date
        StaffAvailability::where([
            'business_id' => $business->id,
            'user_id' => $staff->id,
            'date' => $request->date,
        ])->delete();

        // Create new availabilities
        foreach ($request->availabilities as $availability) {
            StaffAvailability::create([
                'business_id' => $business->id,
                'user_id' => $staff->id,
                'venue_id' => $business->primaryVenue->id,
                'date' => $request->date,
                'start_time' => $availability['start_time'] . ':00',
                'end_time' => $availability['end_time'] . ':00',
                'is_available' => $availability['status'] === 'available',
                'status' => $availability['status'],
            ]);
        }

        return back()->with('success', 'Staff availability updated successfully.');
    }

    /**
     * Bulk update staff availability
     */
    public function bulkUpdate(Request $request, User $staff)
    {
        $business = auth()->user()->businesses()->firstOrFail();

        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'is_available' => 'required|boolean',
            'status' => 'required|in:available,unavailable,on-leave',
        ]);

        foreach ($request->dates as $date) {
            StaffAvailability::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'user_id' => $staff->id,
                    'venue_id' => $business->primaryVenue->id,
                    'date' => $date,
                ],
                [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'is_available' => $request->is_available,
                    'status' => $request->status,
                ]
            );
        }

        return back()->with('success', 'Staff availability bulk updated successfully.');
    }
} 