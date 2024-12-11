<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display the specified category.
     */
    public function show(Request $request, Category $category)
    {
        $query = $category->businesses()
            ->active()
            ->verified()
            ->with(['categories', 'primaryVenue'])
            ->withCount('services');

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Apply sorting
        switch ($request->input('sort', 'recommended')) {
            case 'rating':
                $query->orderByDesc('average_rating');
                break;
            case 'reviews':
                $query->orderByDesc('reviews_count');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            default:
                // For recommended, we'll use a combination of factors
                $query->orderByDesc('is_featured')
                    ->orderByDesc('average_rating')
                    ->orderByDesc('reviews_count');
                break;
        }

        // Get paginated results
        $businesses = $query->paginate(12)
            ->through(fn ($business) => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'logo' => $business->logo,
                'is_verified' => $business->isVerified(),
                'verified_at' => $business->verified_at,
                'services_count' => $business->services_count,
                'address' => $business->address,
                'categories' => $business->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]),
            ]);

        return Inertia::render('Categories/Show', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
            ],
            'businesses' => $businesses,
            'filters' => $request->only(['search', 'sort', 'services']),
        ]);
    }
} 