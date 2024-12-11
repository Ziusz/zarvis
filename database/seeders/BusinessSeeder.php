<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some staff users
        $staffUsers = User::factory()->count(20)->create();

        // Create single-location businesses
        Business::factory()
            ->count(5)
            ->singleLocation()
            ->active()
            ->verified()
            ->create()
            ->each(function ($business) use ($staffUsers) {
                // Get random categories for services
                $categories = Category::inRandomOrder()->limit(2)->get();
                
                // Create services for each category
                $categories->each(function ($category) use ($business, $staffUsers) {
                    // Create 1-2 services per category
                    $services = Service::factory()
                        ->count(rand(1, 2))
                        ->forBusiness($business)
                        ->forCategory($category)
                        ->active()
                        ->create();

                    // Assign random staff to each service
                    $services->each(function ($service) use ($business, $staffUsers) {
                        $service->staff()->attach(
                            $staffUsers->random(rand(1, 3))->pluck('id')->toArray(),
                            [
                                'venue_id' => $business->primaryVenue->id,
                                'status' => 'active',
                            ]
                        );
                    });
                });
            });

        // Create multi-location businesses
        Business::factory()
            ->count(3)
            ->multiLocation()
            ->active()
            ->verified()
            ->create()
            ->each(function ($business) use ($staffUsers) {
                // Create 2-3 venues for each business
                $venues = Venue::factory()
                    ->count(rand(2, 3))
                    ->forMultiLocation($business)
                    ->active()
                    ->create();

                // Get random categories for services
                $categories = Category::inRandomOrder()->limit(2)->get();
                
                // Create services for each category
                $categories->each(function ($category) use ($business, $venues, $staffUsers) {
                    // Create 1-2 services per category
                    $services = Service::factory()
                        ->count(rand(1, 2))
                        ->forBusiness($business)
                        ->forCategory($category)
                        ->active()
                        ->create();

                    // For each service
                    $services->each(function ($service) use ($venues, $staffUsers) {
                        // Attach to random venues with custom pricing
                        $venues->random(rand(1, $venues->count()))->each(function ($venue) use ($service) {
                            $service->venues()->attach($venue->id, [
                                'price' => fake()->randomFloat(2, 
                                    $service->price * 0.8,  // 20% lower
                                    $service->price * 1.2   // 20% higher
                                ),
                                'duration' => $service->duration,
                                'capacity' => $service->capacity,
                                'status' => 'active',
                            ]);
                        });

                        // Assign random staff to each service-venue combination
                        $service->venues->each(function ($venue) use ($service, $staffUsers) {
                            $service->staff()->attach(
                                $staffUsers->random(rand(1, 3))->pluck('id')->toArray(),
                                [
                                    'venue_id' => $venue->id,
                                    'status' => 'active',
                                ]
                            );
                        });
                    });
                });
            });

        // Create some inactive/pending businesses for testing
        Business::factory()
            ->count(2)
            ->state(['status' => 'pending'])
            ->create();

        Business::factory()
            ->count(1)
            ->state(['status' => 'suspended'])
            ->create();
    }
} 