<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
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
                'address' => $business->address,
                'is_verified' => $business->isVerified(),
                'business_hours' => $business->business_hours,
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
        // TODO: Implement business creation
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