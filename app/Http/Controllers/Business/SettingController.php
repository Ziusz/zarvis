<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Show the business settings form.
     */
    public function edit(Request $request)
    {
        return redirect()->route('business.settings.profile');
    }

    /**
     * Show the business profile settings.
     */
    public function profile(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();
        return Inertia::render('Business/Settings/Profile', [
            'business' => $business,
        ]);
    }

    /**
     * Show the working hours settings.
     */
    public function hours(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();
        return Inertia::render('Business/Settings/Hours', [
            'business' => $business,
        ]);
    }

    /**
     * Show the services settings.
     */
    public function services(Request $request)
    {
        // Get the business with services
        $business = $request->user()
            ->businesses()
            ->with(['services' => function($query) {
                $query->orderBy('name');
            }])
            ->firstOrFail();

        // Map services for the business data
        $mappedServices = $business->services->map(fn ($service) => [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration' => $service->duration,
            'price' => $service->price,
            'capacity' => $service->capacity,
            'status' => $service->status,
        ])->values()->all();

        // Create business data with services
        $businessData = [
            'id' => $business->id,
            'name' => $business->name,
            'services' => $mappedServices,
        ];

        // Add debug info
        \Log::info('Services page data:', [
            'business_id' => $business->id,
            'services_count' => count($mappedServices),
            'services' => $mappedServices
        ]);

        return Inertia::render('Business/Settings/Services', [
            'business' => $businessData,
        ]);
    }

    /**
     * Show the staff settings.
     */
    public function staff(Request $request)
    {
        // Get the business with both staff members and services
        $business = $request->user()
            ->businesses()
            ->with(['staffMembers' => function($query) {
                $query->withPivot(['role', 'specialties', 'experience', 'languages', 'status'])
                      ->with('services');
            }])
            ->with(['services' => function($query) {
                $query->where('status', 'active')
                      ->orderBy('name');
            }])
            ->firstOrFail();

        // Map services for the business data
        $mappedServices = $business->services->map(fn ($service) => [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration' => $service->duration,
            'price' => $service->price,
            'capacity' => $service->capacity,
            'status' => $service->status,
        ])->values()->all();

        // Map staff members
        $mappedStaff = $business->staffMembers->map(fn ($staff) => [
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

        // Create business data with services
        $businessData = [
            'id' => $business->id,
            'name' => $business->name,
            'services' => $mappedServices,
        ];

        return Inertia::render('Business/Settings/Staff', [
            'business' => $businessData,
            'staff' => $mappedStaff,
        ]);
    }

    /**
     * Update the business profile settings.
     */
    public function updateProfile(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'street_address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'postal_code' => ['required', 'string'],
            'nip' => ['nullable', 'string', 'size:10'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'website' => ['nullable', 'url'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $validated['logo'] = $request->file('logo')->store('business/logos', 'public');
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($business->cover_image) {
                Storage::disk('public')->delete($business->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('business/covers', 'public');
        }

        $business->update($validated);

        return back()->with('success', 'Business profile updated successfully!');
    }

    /**
     * Update the working hours settings.
     */
    public function updateHours(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'working_hours' => ['required', 'array'],
            'working_hours.*.is_open' => ['required', 'boolean'],
            'working_hours.*.start' => ['required', 'string'],
            'working_hours.*.end' => ['required', 'string'],
        ]);

        // Convert working_hours to opening_hours for database
        $opening_hours = collect($validated['working_hours'])->map(function ($hours) {
            return [
                'open' => $hours['start'],
                'close' => $hours['end'],
                'closed' => !$hours['is_open'],
            ];
        })->toArray();

        $business->update(['opening_hours' => $opening_hours]);

        return back()->with('success', 'Working hours updated successfully!');
    }
} 