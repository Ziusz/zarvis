<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Show the booking creation form.
     */
    public function create(Business $business, Service $service)
    {
        // Check if service belongs to business
        if ($service->business_id !== $business->id) {
            abort(404);
        }

        // Get available dates (next 14 days)
        $dates = collect(CarbonPeriod::create(
            now()->startOfDay(),
            now()->addDays(14)->endOfDay()
        ))->map(fn ($date) => $date->format('Y-m-d'));

        return Inertia::render('Bookings/Create', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'logo' => $business->logo,
            ],
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'duration' => $service->duration,
                'price' => $service->price,
                'capacity' => $service->capacity,
            ],
            'venues' => $business->venues()
                ->active()
                ->get()
                ->map(fn ($venue) => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                ]),
            'dates' => $dates,
        ]);
    }

    /**
     * Get available time slots for a given date.
     */
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'venue_id' => 'required|exists:venues,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($request->date);
        $service = Service::findOrFail($request->service_id);

        // Get business hours for the day
        $business = Business::findOrFail($request->business_id);
        $businessHours = $business->business_hours[strtolower($date->format('l'))] ?? null;

        if (!$businessHours) {
            return response()->json([]);
        }

        // Generate time slots based on service duration
        $slots = [];
        $startTime = Carbon::parse($businessHours[0]);
        $endTime = Carbon::parse($businessHours[1]);

        while ($startTime->copy()->addMinutes($service->duration) <= $endTime) {
            $endSlot = $startTime->copy()->addMinutes($service->duration);
            
            // Check if slot is available
            $isAvailable = TimeSlot::where('date', $date->format('Y-m-d'))
                ->where('start_time', $startTime->format('H:i:s'))
                ->where('service_id', $service->id)
                ->where('venue_id', $request->venue_id)
                ->where('is_available', true)
                ->where('status', 'available')
                ->exists();

            $slots[] = [
                'id' => $startTime->format('H:i'),
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endSlot->format('H:i'),
                'is_available' => $isAvailable,
            ];

            $startTime->addMinutes($service->duration);
        }

        return response()->json($slots);
    }

    /**
     * Get available staff for a given time slot.
     */
    public function getAvailableStaff(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'venue_id' => 'required|exists:venues,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|array',
            'time_slot.start_time' => 'required|date_format:H:i',
            'time_slot.end_time' => 'required|date_format:H:i|after:time_slot.start_time',
        ]);

        $date = Carbon::parse($request->date);
        $startTime = Carbon::parse($request->time_slot['start_time']);
        $endTime = Carbon::parse($request->time_slot['end_time']);

        // Get available staff members
        $availableStaff = User::whereHas('staffAvailabilities', function ($query) use ($date, $startTime, $endTime, $request) {
            $query->where('date', $date->format('Y-m-d'))
                ->where('venue_id', $request->venue_id)
                ->where('is_available', true)
                ->where('status', 'available')
                ->where('start_time', '<=', $startTime->format('H:i:s'))
                ->where('end_time', '>=', $endTime->format('H:i:s'));
        })->whereHas('services', function ($query) use ($request) {
            $query->where('services.id', $request->service_id)
                ->where('venue_id', $request->venue_id)
                ->where('status', 'active');
        })->get();

        return response()->json($availableStaff->map(fn ($staff) => [
            'id' => $staff->id,
            'name' => $staff->name,
            'avatar' => $staff->profile_photo_url,
            'role' => $staff->role,
            'rating' => $staff->average_rating,
            'reviews_count' => $staff->reviews_count,
            'specialties' => $staff->specialties,
            'experience' => $staff->experience,
            'languages' => $staff->languages,
            'next_available' => $staff->next_available,
        ]));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'venue_id' => 'required|exists:venues,id',
            'staff_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|array',
            'time_slot.start_time' => 'required|date_format:H:i',
            'time_slot.end_time' => 'required|date_format:H:i|after:time_slot.start_time',
            'participants' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($request->service_id);
        
        // Validate participants count
        if ($request->participants > $service->capacity) {
            throw ValidationException::withMessages([
                'participants' => ['The number of participants exceeds the service capacity.'],
            ]);
        }

        // Check if slot is still available
        $startTime = Carbon::parse($request->date . ' ' . $request->time_slot['start_time']);
        $endTime = Carbon::parse($request->date . ' ' . $request->time_slot['end_time']);

        $isAvailable = TimeSlot::where('date', $request->date)
            ->where('start_time', $startTime->format('H:i:s'))
            ->where('service_id', $service->id)
            ->where('venue_id', $request->venue_id)
            ->where('is_available', true)
            ->where('status', 'available')
            ->exists();

        if (!$isAvailable) {
            throw ValidationException::withMessages([
                'time_slot' => ['This time slot is no longer available.'],
            ]);
        }

        // Create booking
        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'business_id' => $request->business_id,
                'venue_id' => $request->venue_id,
                'service_id' => $request->service_id,
                'staff_id' => $request->staff_id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'participants' => $request->participants,
                'total_price' => $service->price * $request->participants,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $request->notes,
                'customer_details' => [
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone,
                ],
                'service_details' => [
                    'name' => $service->name,
                    'duration' => $service->duration,
                    'price' => $service->price,
                ],
            ]);

            // Update time slot availability
            TimeSlot::where('date', $request->date)
                ->where('start_time', $startTime->format('H:i:s'))
                ->where('service_id', $service->id)
                ->where('venue_id', $request->venue_id)
                ->update([
                    'booked' => DB::raw('booked + ' . $request->participants),
                    'status' => DB::raw('CASE WHEN (booked + ' . $request->participants . ') >= capacity THEN \'fully-booked\' ELSE status END'),
                ]);

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        // Ensure user can only view their own bookings
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'payment_status' => $booking->payment_status,
                'payment_status_label' => $booking->payment_status_label,
                'payment_method' => $booking->payment_method,
                'total_price' => $booking->total_price,
                'participants' => $booking->participants,
                'notes' => $booking->notes,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'can_be_cancelled' => $booking->canBeCancelled(),
                'can_be_rescheduled' => $booking->canBeRescheduled(),
                'service' => [
                    'id' => $booking->service->id,
                    'name' => $booking->service->name,
                    'description' => $booking->service->description,
                    'duration' => $booking->service->duration,
                    'price' => $booking->service->price,
                ],
                'business' => [
                    'id' => $booking->business->id,
                    'name' => $booking->business->name,
                    'slug' => $booking->business->slug,
                ],
                'venue' => [
                    'id' => $booking->venue->id,
                    'name' => $booking->venue->name,
                    'address' => $booking->venue->address,
                ],
                'staff' => $booking->staff ? [
                    'id' => $booking->staff->id,
                    'name' => $booking->staff->name,
                    'avatar' => $booking->staff->profile_photo_url,
                    'role' => $booking->staff->role,
                ] : null,
            ],
        ]);
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Ensure user can only cancel their own bookings
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure booking can be cancelled
        if (!$booking->canBeCancelled()) {
            throw ValidationException::withMessages([
                'booking' => ['This booking cannot be cancelled.'],
            ]);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now(),
            ]);

            // Update time slot availability
            TimeSlot::where('date', $booking->start_time->format('Y-m-d'))
                ->where('start_time', $booking->start_time->format('H:i:s'))
                ->where('service_id', $booking->service_id)
                ->where('venue_id', $booking->venue_id)
                ->update([
                    'booked' => DB::raw('booked - ' . $booking->participants),
                    'status' => 'available',
                ]);

            DB::commit();

            return back()->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reschedule the specified booking.
     */
    public function reschedule(Request $request, Booking $booking)
    {
        // Ensure user can only reschedule their own bookings
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure booking can be rescheduled
        if (!$booking->canBeRescheduled()) {
            throw ValidationException::withMessages([
                'booking' => ['This booking cannot be rescheduled.'],
            ]);
        }

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|array',
            'time_slot.start_time' => 'required|date_format:H:i',
            'time_slot.end_time' => 'required|date_format:H:i|after:time_slot.start_time',
            'staff_id' => 'required|exists:users,id',
        ]);

        $startTime = Carbon::parse($request->date . ' ' . $request->time_slot['start_time']);
        $endTime = Carbon::parse($request->date . ' ' . $request->time_slot['end_time']);

        DB::beginTransaction();
        try {
            // Free up old time slot
            TimeSlot::where('date', $booking->start_time->format('Y-m-d'))
                ->where('start_time', $booking->start_time->format('H:i:s'))
                ->where('service_id', $booking->service_id)
                ->where('venue_id', $booking->venue_id)
                ->update([
                    'booked' => DB::raw('booked - ' . $booking->participants),
                    'status' => 'available',
                ]);

            // Update booking
            $booking->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'staff_id' => $request->staff_id,
            ]);

            // Update new time slot
            TimeSlot::where('date', $request->date)
                ->where('start_time', $startTime->format('H:i:s'))
                ->where('service_id', $booking->service_id)
                ->where('venue_id', $booking->venue_id)
                ->update([
                    'booked' => DB::raw('booked + ' . $booking->participants),
                    'status' => DB::raw('CASE WHEN (booked + ' . $booking->participants . ') >= capacity THEN \'fully-booked\' ELSE status END'),
                ]);

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking rescheduled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
