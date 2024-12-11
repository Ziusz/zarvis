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

                // Create 1-3 venues for multi-location businesses
                $venueCount = $business->is_single_location ? 1 : rand(1, 3);
                
                // Create venues
                Venue::factory()
                    ->count($venueCount)
                    ->sequence(fn ($sequence) => [
                        'business_id' => $business->id,
                        'name' => $sequence->index === 0 && $business->is_single_location
                            ? $business->name
                            : $business->name . ' - ' . fake()->city(),
                        'is_primary' => $sequence->index === 0,
                    ])
                    ->create();

                // Create 3-8 services per business
                Service::factory()
                    ->count(rand(3, 8))
                    ->sequence(fn ($sequence) => [
                        'business_id' => $business->id,
                        'category_id' => $categories->random()->id,
                        'name' => fake()->randomElement([
                            'Personal Training',
                            'Group Fitness Class',
                            'Yoga Session',
                            'Swimming Lesson',
                            'Tennis Coaching',
                            'Basketball Training',
                            'Soccer Practice',
                            'Dance Class',
                            'Martial Arts Training',
                            'CrossFit Session',
                            'Pilates Class',
                            'Boxing Training',
                            'Cycling Class',
                            'Strength Training',
                            'Cardio Session',
                            'Stretching Class',
                            'Meditation Session',
                            'Sports Massage',
                            'Nutrition Consultation',
                            'Fitness Assessment',
                        ]) . ' - ' . fake()->word() . ' ' . Str::random(4),
                    ])
                    ->create();

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