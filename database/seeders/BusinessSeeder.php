<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create staff members first
        $staffMembers = User::factory()
            ->count(50)
            ->staff()
            ->create();

        // Get all categories
        $categories = Category::all();

        // Create 20 businesses
        Business::factory()
            ->count(20)
            ->sequence(fn ($sequence) => [
                'name' => fake()->company() . ' ' . ['Sports', 'Fitness', 'Gym', 'Club', 'Center'][rand(0, 4)],
                'status' => 'active',
                'verified_at' => rand(0, 1) ? now() : null, // 50% chance of being verified
                'is_featured' => rand(0, 4) === 0, // 20% chance of being featured
            ])
            ->create()
            ->each(function ($business) use ($categories, $staffMembers) {
                // Attach 1-3 random categories
                $business->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')->toArray()
                );

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

                // Create additional venues for multi-location businesses (1-2 more)
                if (!$business->is_single_location) {
                    Venue::factory()
                        ->count(rand(1, 2))
                        ->create([
                            'business_id' => $business->id,
                            'is_primary' => false,
                        ]);
                }

                // Create 3-8 services per business
                Service::factory()
                    ->count(rand(3, 8))
                    ->create([
                        'business_id' => $business->id,
                    ]);

                // Assign 2-5 random staff members to the business
                $business->staffMembers()->attach(
                    $staffMembers->random(rand(2, 5))->pluck('id')->toArray(),
                    [
                        'role' => 'staff',
                        'specialties' => json_encode(['Personal Training', 'Group Fitness']),
                        'experience' => 'Over 5 years of experience',
                        'languages' => json_encode(['English', 'Spanish']),
                    ]
                );
            });
    }
} 