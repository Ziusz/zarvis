<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Services\TimeSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    protected TimeSlotService $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->timeSlotService = $timeSlotService;
    }

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

        \Log::info('Getting time slots', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'date' => $request->date,
            'staff_id' => $request->staff_id,
            'business_hours' => $business->opening_hours,
            'service_duration' => $service->duration,
        ]);

        $slots = $this->timeSlotService->getAvailableTimeSlots($business, $service, $request->date);

        \Log::info('Time slots response', [
            'slots_count' => $slots->count(),
            'first_slot' => $slots->first(),
            'last_slot' => $slots->last(),
        ]);

        $formatted_slots = $slots->map(function ($slot) {
            if (is_array($slot)) {
                return $slot;
            }
            
            return [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'booked' => $slot->booked,
                'is_available' => $slot->is_available,
                'status' => $slot->status,
            ];
        });

        return response()->json([
            'slots' => $formatted_slots,
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
            'time_slot_id' => 'required|string',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);

        // Parse the time slot ID
        [$date, $start_time, $end_time] = explode('_', $request->time_slot_id);
        
        // Find or get the time slot
        $timeSlot = TimeSlot::firstOrCreate(
            [
                'business_id' => $business->id,
                'service_id' => $service->id,
                'date' => $date,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ],
            [
                'venue_id' => $business->primaryVenue->id,
                'capacity' => $service->capacity,
                'booked' => 0,
                'status' => 'available',
            ]
        );
        
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
            'time_slot_id' => 'required|string',
            'staff_id' => 'nullable|exists:users,id',
            'participants' => 'required|integer|min:1',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);

        // Parse the time slot ID
        [$date, $start_time, $end_time] = explode('_', $request->time_slot_id);

        // Check if the time slot exists and is available
        $timeSlot = TimeSlot::firstOrCreate(
            [
                'business_id' => $business->id,
                'service_id' => $service->id,
                'date' => $date,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ],
            [
                'venue_id' => $business->primaryVenue->id,
                'capacity' => $service->capacity,
                'booked' => 0,
                'status' => 'available',
            ]
        );

        // Check if the time slot is still available
        if (!$timeSlot->isAvailable() || $timeSlot->getRemainingCapacity() < $request->participants) {
            return response()->json([
                'message' => 'This time slot is no longer available.',
            ], 422);
        }

        // Check if staff is assigned
        $hasStaff = $request->staff_id || $timeSlot->staff_id;
        $bookingStatus = $hasStaff ? 'pending' : 'unassigned';

        // Create the booking
        $booking = Booking::create([
            'user_id' => auth()->id(),
            'business_id' => $business->id,
            'venue_id' => $timeSlot->venue_id,
            'service_id' => $service->id,
            'staff_id' => $request->staff_id ?? $timeSlot->staff_id,
            'start_time' => Carbon::parse($date . ' ' . $start_time),
            'end_time' => Carbon::parse($date . ' ' . $end_time),
            'participants' => $request->participants,
            'total_price' => $service->price * $request->participants,
            'status' => $bookingStatus,
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

        // If no staff is assigned, notify business about unassigned booking
        if (!$hasStaff) {
            // TODO: Implement notification system
            // Notification::send($business->owner, new UnassignedBookingNotification($booking));
        }

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'start_time' => $booking->start_time,
                'total_price' => $booking->total_price,
                'needs_staff' => !$hasStaff,
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

    public function getAvailableDates(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:users,id',
        ]);

        $business = Business::findOrFail($request->business_id);
        $service = Service::findOrFail($request->service_id);
        
        // Get dates for the next 30 days
        $dates = collect();
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(30)->endOfDay();

        // Get opening hours
        $opening_hours = $business->opening_hours;
        if (is_string($opening_hours)) {
            try {
                $opening_hours = json_decode($opening_hours, true);
            } catch (\Exception $e) {
                $opening_hours = [];
            }
        }

        // Loop through each day
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $day_of_week = strtolower($date->format('l'));
            $day_hours = $opening_hours[$day_of_week] ?? null;

            // Check if the business is open on this day
            // Handle both old and new format
            $is_open = false;
            if ($day_hours) {
                if (isset($day_hours['is_open'])) {
                    // New format
                    $is_open = $day_hours['is_open'];
                } else {
                    // Old format
                    $is_open = !($day_hours['closed'] ?? true);
                }
            }

            if ($is_open) {
                $dates->push($date->format('Y-m-d'));
            }
        }

        \Log::info('Available dates:', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'opening_hours' => $opening_hours,
            'dates' => $dates->values()->all(),
        ]);

        return response()->json([
            'dates' => $dates->values()->all(),
        ]);
    }
}
