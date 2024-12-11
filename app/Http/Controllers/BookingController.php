<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Models\Booking;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Show the booking creation form.
     */
    public function create(Business $business, Service $service)
    {
        // Check if the service belongs to the business
        if ($service->business_id !== $business->id) {
            abort(404);
        }

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
                'slug' => $service->slug,
                'description' => $service->description,
                'duration' => $service->duration,
                'price' => $service->price,
                'capacity' => $service->capacity,
                'images' => $service->images,
                'settings' => $service->settings,
            ],
        ]);
    }

    /**
     * Get available time slots for a given date.
     */
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'staff_id' => 'nullable|exists:users,id',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date)->startOfDay();
        
        $query = TimeSlot::where('business_id', $business->id)
            ->where('service_id', $service->id)
            ->whereDate('date', $date)
            ->available()
            ->orderBy('start_time');

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $timeSlots = $query->get()->map(fn ($slot) => [
            'id' => $slot->id,
            'start_time' => $slot->start_time->format('H:i'),
            'end_time' => $slot->end_time->format('H:i'),
            'capacity' => $slot->capacity,
            'booked' => $slot->booked,
            'is_available' => $slot->isAvailable(),
            'remaining_capacity' => $slot->getRemainingCapacity(),
        ]);

        return response()->json([
            'timeSlots' => $timeSlots,
            'date' => $date->toDateString(),
        ]);
    }

    /**
     * Get available staff for a given time slot.
     */
    public function getAvailableStaff(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);
        $timeSlot = TimeSlot::findOrFail($request->time_slot_id);
        
        // First, try to get the staff member assigned to this time slot
        if ($timeSlot->staff_id) {
            $assignedStaff = $business->staffMembers()
                ->where('users.id', $timeSlot->staff_id)
                ->first();

            if ($assignedStaff) {
                return response()->json([
                    'staff' => [[
                        'id' => $assignedStaff->id,
                        'name' => $assignedStaff->name,
                        'profile_photo_url' => $assignedStaff->profile_photo_url,
                        'specialties' => $assignedStaff->specialties,
                        'experience' => $assignedStaff->experience,
                        'languages' => $assignedStaff->languages,
                        'average_rating' => $assignedStaff->average_rating,
                        'reviews_count' => $assignedStaff->reviews_count,
                    ]],
                ]);
            }
        }

        // If no assigned staff or they're not available, get all available staff
        $staff = $business->staffMembers()
            ->whereHas('staffAvailabilities', function ($query) use ($timeSlot) {
                $query->where('date', $timeSlot->date)
                    ->where('is_available', true)
                    ->where('status', 'available')
                    ->whereTime('start_time', '<=', $timeSlot->start_time)
                    ->whereTime('end_time', '>=', $timeSlot->end_time);
            })
            ->get()
            ->map(fn ($staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'profile_photo_url' => $staff->profile_photo_url,
                'specialties' => $staff->specialties,
                'experience' => $staff->experience,
                'languages' => $staff->languages,
                'average_rating' => $staff->average_rating,
                'reviews_count' => $staff->reviews_count,
            ]);

        return response()->json([
            'staff' => $staff,
        ]);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'staff_id' => 'nullable|exists:users,id',
            'participants' => 'required|integer|min:1',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);
        $timeSlot = TimeSlot::findOrFail($request->time_slot_id);

        // Check if the time slot is still available
        if (!$timeSlot->isAvailable() || $timeSlot->getRemainingCapacity() < $request->participants) {
            return response()->json([
                'message' => 'This time slot is no longer available.',
            ], 422);
        }

        // Create the booking
        $booking = Booking::create([
            'user_id' => auth()->id(),
            'business_id' => $business->id,
            'venue_id' => $timeSlot->venue_id,
            'service_id' => $service->id,
            'staff_id' => $request->staff_id ?? $timeSlot->staff_id,
            'start_time' => Carbon::parse($timeSlot->date)->setTimeFromTimeString($timeSlot->start_time),
            'end_time' => Carbon::parse($timeSlot->date)->setTimeFromTimeString($timeSlot->end_time),
            'participants' => $request->participants,
            'total_price' => $service->price * $request->participants,
            'status' => 'pending',
            'payment_status' => 'pending',
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
        $timeSlot->booked += $request->participants;
        $timeSlot->status = $timeSlot->booked >= $timeSlot->capacity ? 'fully-booked' : 'available';
        $timeSlot->save();

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'start_time' => $booking->start_time,
                'total_price' => $booking->total_price,
            ],
        ]);
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        // Check if the user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Load the relationships
        $booking->load(['business', 'venue', 'staff', 'service']);

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'payment_status' => $booking->payment_status,
                'payment_status_label' => $booking->payment_status_label,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'participants' => $booking->participants,
                'total_price' => $booking->total_price,
                'customer_details' => $booking->customer_details,
                'service_details' => $booking->service_details,
                'notes' => $booking->notes,
                'can_be_cancelled' => $booking->canBeCancelled(),
                'can_be_rescheduled' => $booking->canBeRescheduled(),
                'business' => [
                    'id' => $booking->business->id,
                    'name' => $booking->business->name,
                    'slug' => $booking->business->slug,
                    'logo' => $booking->business->logo,
                ],
                'venue' => [
                    'id' => $booking->venue->id,
                    'name' => $booking->venue->name,
                    'address' => $booking->venue->address,
                ],
                'service' => [
                    'id' => $booking->service->id,
                    'name' => $booking->service->name,
                    'description' => $booking->service->description,
                    'duration' => $booking->service->duration,
                    'price' => $booking->service->price,
                ],
                'staff' => $booking->staff ? [
                    'id' => $booking->staff->id,
                    'name' => $booking->staff->name,
                    'profile_photo_url' => $booking->staff->profile_photo_url,
                    'role' => $booking->staff->role,
                ] : null,
            ],
        ]);
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        // Check if the user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if the booking can be cancelled
        if (!$booking->canBeCancelled()) {
            return response()->json([
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Free up the time slot
        $timeSlot = TimeSlot::where([
            'business_id' => $booking->business_id,
            'venue_id' => $booking->venue_id,
            'service_id' => $booking->service_id,
            'date' => $booking->start_time->format('Y-m-d'),
            'start_time' => $booking->start_time->format('H:i:s'),
        ])->first();

        if ($timeSlot) {
            $timeSlot->booked -= $booking->participants;
            $timeSlot->status = 'available';
            $timeSlot->save();
        }

        return back();
    }

    /**
     * Reschedule the specified booking.
     */
    public function reschedule(Request $request, Booking $booking)
    {
        // Check if the user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if the booking can be rescheduled
        if (!$booking->canBeRescheduled()) {
            return response()->json([
                'message' => 'This booking cannot be rescheduled.',
            ], 422);
        }

        $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'staff_id' => 'nullable|exists:users,id',
        ]);

        $newTimeSlot = TimeSlot::findOrFail($request->time_slot_id);

        // Check if the new time slot is available
        if (!$newTimeSlot->isAvailable() || $newTimeSlot->getRemainingCapacity() < $booking->participants) {
            return response()->json([
                'message' => 'The selected time slot is not available.',
            ], 422);
        }

        // Free up the old time slot
        $oldTimeSlot = TimeSlot::where([
            'business_id' => $booking->business_id,
            'venue_id' => $booking->venue_id,
            'service_id' => $booking->service_id,
            'date' => $booking->start_time->format('Y-m-d'),
            'start_time' => $booking->start_time->format('H:i:s'),
        ])->first();

        if ($oldTimeSlot) {
            $oldTimeSlot->booked -= $booking->participants;
            $oldTimeSlot->status = 'available';
            $oldTimeSlot->save();
        }

        // Update the booking
        $booking->update([
            'staff_id' => $request->staff_id ?? $newTimeSlot->staff_id,
            'start_time' => Carbon::parse($newTimeSlot->date)->setTimeFromTimeString($newTimeSlot->start_time),
            'end_time' => Carbon::parse($newTimeSlot->date)->setTimeFromTimeString($newTimeSlot->end_time),
        ]);

        // Update the new time slot
        $newTimeSlot->booked += $booking->participants;
        $newTimeSlot->status = $newTimeSlot->booked >= $newTimeSlot->capacity ? 'fully-booked' : 'available';
        $newTimeSlot->save();

        return back();
    }
}
