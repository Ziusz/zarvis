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
        $business = $request->user()->ownedBusinesses()->firstOrFail();

        return Inertia::render('Business/Settings', [
            'business' => $business,
        ]);
    }

    /**
     * Update the business settings.
     */
    public function update(Request $request)
    {
        $business = $request->user()->ownedBusinesses()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'working_hours' => ['required', 'array'],
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

        return redirect()->back()->with('success', 'Business settings updated successfully!');
    }
} 