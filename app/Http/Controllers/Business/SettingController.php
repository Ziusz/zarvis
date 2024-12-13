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
        $business = $request->user()->businesses()->with('services')->firstOrFail();

        // Default opening hours
        $defaultHours = [
            'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'saturday' => ['open' => '09:00', 'close' => '17:00', 'closed' => true],
            'sunday' => ['open' => '09:00', 'close' => '17:00', 'closed' => true],
        ];

        // Handle opening hours
        if (!$business->opening_hours) {
            $business->opening_hours = $defaultHours;
        }

        // Convert opening hours format for frontend
        if ($business->opening_hours) {
            $business->opening_hours = collect($business->opening_hours)->map(function ($hours) {
                return [
                    'is_open' => !($hours['closed'] ?? false),
                    'start' => $hours['open'] ?? '09:00',
                    'end' => $hours['close'] ?? '17:00',
                ];
            })->toArray();
        }

        return Inertia::render('Business/Settings', [
            'business' => $business,
        ]);
    }

    /**
     * Update the business settings.
     */
    public function update(Request $request)
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
            'working_hours' => ['required', 'array'],
            'working_hours.*.is_open' => ['required', 'boolean'],
            'working_hours.*.start' => ['required', 'string'],
            'working_hours.*.end' => ['required', 'string'],
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

        // Convert working_hours to opening_hours for database
        $validated['opening_hours'] = collect($validated['working_hours'])->map(function ($hours) {
            return [
                'open' => $hours['start'],
                'close' => $hours['end'],
                'closed' => !$hours['is_open'],
            ];
        })->toArray();
        unset($validated['working_hours']);

        $business->update($validated);

        return redirect()->back()->with('success', 'Business settings updated successfully!');
    }
} 