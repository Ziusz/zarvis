<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BusinessController extends Controller
{
    /**
     * Display the specified business.
     */
    public function show(Request $request, Business $business)
    {
        // Load relationships
        $business->load(['categories', 'primaryVenue']);

        // Get paginated services
        $services = $business->services()
            ->active()
            ->with('category')
            ->orderBy('name')
            ->paginate(12)
            ->through(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'duration' => $service->duration,
                'price' => $service->price,
                'capacity' => $service->capacity,
                'images' => $service->images,
                'settings' => $service->settings,
                'category' => $service->category ? [
                    'id' => $service->category->id,
                    'name' => $service->category->name,
                    'slug' => $service->category->slug,
                ] : null,
            ]);

        // Format business hours
        $businessHours = [];
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($daysOfWeek as $day) {
            $businessHours[$day] = $business->business_hours[$day] ?? null;
        }

        // Get venue details
        $venue = $business->primaryVenue;
        $address = $venue ? [
            'full' => $venue->address,
            'latitude' => $venue->latitude,
            'longitude' => $venue->longitude,
            'map_url' => sprintf(
                'https://www.google.com/maps/search/?api=1&query=%s,%s',
                $venue->latitude,
                $venue->longitude
            ),
        ] : null;

        return Inertia::render('Businesses/Show', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'logo' => $business->logo,
                'cover_image' => $business->cover_image,
                'email' => $business->email,
                'phone' => $business->phone,
                'website' => $business->website,
                'address' => $address,
                'is_verified' => $business->isVerified(),
                'verified_at' => $business->verified_at,
                'business_hours' => $businessHours,
                'social_links' => $business->social_links,
                'categories' => $business->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]),
            ],
            'services' => $services,
        ]);
    }

    /**
     * Show the form for creating a new business.
     */
    public function create()
    {
        return Inertia::render('Businesses/Create');
    }

    /**
     * Store a newly created business in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'nip' => [
                'required',
                'string',
                'regex:/^\d{10}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validateNIP($value)) {
                        $fail('The NIP number is invalid.');
                    }
                },
            ],
            'phone' => 'required|string',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'opening_hours' => 'required|array',
        ]);

        $business = Business::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'street_address' => $request->street_address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'nip' => $request->nip,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'opening_hours' => $request->opening_hours,
            'owner_id' => auth()->id(),
            'status' => 'active',
        ]);

        // Attach the owner as staff
        $business->staffMembers()->attach(auth()->id(), [
            'role' => 'owner',
            'status' => 'active',
        ]);

        // Create primary venue
        $business->venues()->create([
            'name' => $business->name,
            'slug' => $business->slug,
            'description' => $business->description,
            'address' => $business->full_address,
            'contact_info' => [
                'phone' => $business->phone,
                'email' => $business->email,
            ],
            'business_hours' => $business->opening_hours,
            'status' => 'active',
            'is_primary' => true,
        ]);

        return redirect()->route('business.settings', $business)
            ->with('success', 'Business created successfully!');
    }

    /**
     * Validate NIP number using the checksum algorithm.
     */
    private function validateNIP(string $nip): bool
    {
        if (strlen($nip) !== 10) {
            return false;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += $nip[$i] * $weights[$i];
        }

        $checksum = $sum % 11;
        if ($checksum === 10) {
            $checksum = 0;
        }

        return $checksum === (int)$nip[9];
    }

    /**
     * Show the form for editing the specified business.
     */
    public function edit(Business $business)
    {
        // TODO: Implement business editing
    }

    /**
     * Update the specified business in storage.
     */
    public function update(Request $request, Business $business)
    {
        // TODO: Implement business updating
    }

    /**
     * Remove the specified business from storage.
     */
    public function destroy(Business $business)
    {
        // TODO: Implement business deletion
    }
} 