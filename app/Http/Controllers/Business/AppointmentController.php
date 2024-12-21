<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display the calendar page.
     */
    public function calendar(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        return Inertia::render('Business/Calendar', [
            'business' => $business,
        ]);
    }

    /**
     * Get appointments for the business.
     */
    public function index(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $appointments = $business->bookings()
            ->with(['customer', 'staff', 'service'])
            ->whereBetween('start_time', [$validated['start_date'], $validated['end_date']])
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'title' => $booking->service->name,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'type' => $booking->type ?? 'client',
                'notes' => $booking->notes,
                'service' => [
                    'id' => $booking->service->id,
                    'name' => $booking->service->name,
                ],
                'customer' => $booking->customer ? [
                    'id' => $booking->customer->id,
                    'name' => $booking->customer->name,
                    'email' => $booking->customer->email,
                    'avatar' => $booking->customer->profile_photo_url,
                ] : null,
                'staff' => $booking->staff ? [
                    'id' => $booking->staff->id,
                    'name' => $booking->staff->name,
                    'email' => $booking->staff->email,
                    'avatar' => $booking->staff->profile_photo_url,
                ] : null,
            ]);

        return response()->json($appointments);
    }

    /**
     * Confirm an appointment.
     */
    public function confirm(Request $request, Booking $booking)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure booking belongs to business
        if ($booking->business_id !== $business->id) {
            abort(404);
        }

        $booking->update([
            'status' => 'confirmed',
        ]);

        return response()->json([
            'message' => 'Appointment confirmed successfully.',
        ]);
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure booking belongs to business
        if ($booking->business_id !== $business->id) {
            abort(404);
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
        ]);
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'staff_id' => ['required', 'exists:users,id'],
            'customer_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Get service to calculate end time
        $service = $business->services()->findOrFail($validated['service_id']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        // Create booking
        $booking = $business->bookings()->create([
            'service_id' => $validated['service_id'],
            'staff_id' => $validated['staff_id'],
            'customer_id' => $validated['customer_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $validated['notes'],
            'status' => 'confirmed', // Since this is created by business
            'type' => 'client',
        ]);

        // Load relationships
        $booking->load(['customer', 'staff', 'service']);

        return response()->json([
            'appointment' => $this->formatBooking($booking),
        ]);
    }

    /**
     * Move an appointment to a new time.
     */
    public function move(Request $request, Booking $booking)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure booking belongs to business
        if ($booking->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'start_time' => ['required', 'date'],
        ]);

        // Calculate new end time based on service duration
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($booking->service->duration);

        $booking->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return response()->json([
            'message' => 'Appointment moved successfully.',
        ]);
    }

    /**
     * Search for customers.
     */
    public function searchCustomers(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $customers = User::where('role', 'customer')
            ->where(function ($query) use ($validated) {
                $query->where('name', 'like', "%{$validated['query']}%")
                    ->orWhere('email', 'like', "%{$validated['query']}%")
                    ->orWhere('phone', 'like', "%{$validated['query']}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->profile_photo_url,
            ]);

        return response()->json($customers);
    }

    /**
     * Format booking data for response.
     */
    private function formatBooking($booking)
    {
        return [
            'id' => $booking->id,
            'title' => $booking->service->name,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'status' => $booking->status,
            'type' => $booking->type ?? 'client',
            'notes' => $booking->notes,
            'service' => [
                'id' => $booking->service->id,
                'name' => $booking->service->name,
            ],
            'customer' => $booking->customer ? [
                'id' => $booking->customer->id,
                'name' => $booking->customer->name,
                'email' => $booking->customer->email,
                'avatar' => $booking->customer->profile_photo_url,
            ] : null,
            'staff' => $booking->staff ? [
                'id' => $booking->staff->id,
                'name' => $booking->staff->name,
                'email' => $booking->staff->email,
                'avatar' => $booking->staff->profile_photo_url,
            ] : null,
        ];
    }
} 