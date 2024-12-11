<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('Welcome', [
            'categories' => Category::query()
                ->root() // Only parent categories
                ->active()
                ->featured()
                ->with(['children' => function ($query) {
                    $query->active();
                }])
                ->get()
                ->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon,
                    'children' => $category->children->map(fn ($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                    ]),
                ]),

            'featuredBusinesses' => Business::query()
                ->active()
                ->verified()
                ->with(['categories', 'primaryVenue'])
                ->withCount(['services', 'venues'])
                ->orderByDesc('created_at')
                ->take(6)
                ->get()
                ->map(fn ($business) => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'description' => $business->description,
                    'logo' => $business->logo,
                    'is_verified' => $business->isVerified(),
                    'services_count' => $business->services_count,
                    'venues_count' => $business->venues_count,
                    'address' => $business->address,
                    'categories' => $business->categories->map(fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ]),
                ]),
        ]);
    }
} 