<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(Request $request)
    {
        // Get the business with services and staff members
        $business = $request->user()
            ->businesses()
            ->with(['services' => function($query) {
                $query->where('status', 'active')
                     ->orderBy('name');
            }])
            ->firstOrFail();

        dd([
            'business_id' => $business->id,
            'services' => $business->services->toArray(),
            'raw_business' => $business->toArray()
        ]);
            
        // Get services from the eager loaded relationship
        $services = $business->services;
        
        $mappedServices = $services->map(fn ($service) => [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration' => $service->duration,
            'price' => $service->price,
            'capacity' => $service->capacity,
            'status' => $service->status,
        ])->values()->all();
        
        // Get staff members
        $staff = $business->staffMembers()
            ->withPivot(['role', 'specialties', 'experience', 'languages', 'status'])
            ->with('services')
            ->get()
            ->map(fn ($staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'avatar' => $staff->profile_photo_url,
                'role' => $staff->pivot->role,
                'specialties' => $staff->pivot->specialties ?? [],
                'experience' => $staff->pivot->experience,
                'languages' => $staff->pivot->languages ?? [],
                'status' => $staff->pivot->status ?? 'active',
                'services' => $staff->services->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                ]),
            ]);

        // Create business data with full services information
        $businessData = [
            'id' => $business->id,
            'name' => $business->name,
            'services' => $mappedServices,
        ];

        return Inertia::render('Business/Settings/Staff', [
            'business' => $businessData,
            'staff' => $staff,
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:staff,manager'],
            'specialties' => ['nullable', 'array'],
            'experience' => ['nullable', 'string'],
            'languages' => ['nullable', 'array'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
        ]);

        // Create user account for staff member
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make(Str::random(12)), // Random password, they'll need to reset it
            'role' => 'staff',
        ]);

        // Attach user to business as staff
        $business->staffMembers()->attach($user->id, [
            'role' => $validated['role'],
            'specialties' => $validated['specialties'] ?? [],
            'experience' => $validated['experience'],
            'languages' => $validated['languages'] ?? [],
            'status' => 'active',
        ]);

        // Attach services if provided
        if (!empty($validated['services'])) {
            $user->services()->attach($validated['services']);
        }

        // TODO: Send welcome email with password reset link

        return back()->with('success', 'Staff member added successfully.');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure staff member belongs to business
        if (!$business->staffMembers()->where('user_id', $staff->id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'phone' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:staff,manager'],
            'specialties' => ['nullable', 'array'],
            'experience' => ['nullable', 'string'],
            'languages' => ['nullable', 'array'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        // Update user details
        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Update pivot data
        $business->staffMembers()->updateExistingPivot($staff->id, [
            'role' => $validated['role'],
            'specialties' => $validated['specialties'] ?? [],
            'experience' => $validated['experience'],
            'languages' => $validated['languages'] ?? [],
            'status' => $validated['status'],
        ]);

        // Sync services
        if (isset($validated['services'])) {
            $staff->services()->sync($validated['services']);
        }

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Request $request, User $staff)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure staff member belongs to business
        if (!$business->staffMembers()->where('user_id', $staff->id)->exists()) {
            abort(404);
        }

        // Detach from business
        $business->staffMembers()->detach($staff->id);

        // Detach from services
        $staff->services()->detach();

        return back()->with('success', 'Staff member removed successfully.');
    }

    /**
     * Update staff member's availability.
     */
    public function updateAvailability(Request $request, User $staff)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure staff member belongs to business
        if (!$business->staffMembers()->where('user_id', $staff->id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'availability' => ['required', 'array'],
            'availability.*.date' => ['required', 'date'],
            'availability.*.start_time' => ['required', 'date_format:H:i'],
            'availability.*.end_time' => ['required', 'date_format:H:i', 'after:availability.*.start_time'],
            'availability.*.is_available' => ['required', 'boolean'],
        ]);

        // Update or create availability records
        foreach ($validated['availability'] as $slot) {
            $staff->staffAvailabilities()->updateOrCreate(
                [
                    'date' => $slot['date'],
                    'business_id' => $business->id,
                ],
                [
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'is_available' => $slot['is_available'],
                    'status' => $slot['is_available'] ? 'available' : 'unavailable',
                ]
            );
        }

        return back()->with('success', 'Staff availability updated successfully.');
    }
} 