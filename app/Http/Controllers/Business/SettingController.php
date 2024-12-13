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
        $business = $request->user()->businesses()->with('services')->firstOrFail();
        return Inertia::render('Business/Settings/Services', [
            'business' => $business,
        ]);
    }

    /**
     * Show the staff settings.
     */
    public function staff(Request $request)
    {
        $business = $request->user()->businesses()->with('staffMembers')->firstOrFail();
        return Inertia::render('Business/Settings/Staff', [
            'business' => $business,
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