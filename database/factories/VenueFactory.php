<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locations = ['Main', 'Downtown', 'Uptown', 'West', 'East', 'North', 'South'];
        $name = $this->faker->randomElement($locations) . ' ' . $this->faker->randomElement(['Branch', 'Location', 'Center']);
        
        return [
            'business_id' => Business::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'contact_info' => [
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->email(),
                'manager' => $this->faker->name(),
            ],
            'business_hours' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['10:00', '16:00'],
                'sunday' => null, // closed
            ],
            'images' => [
                'main' => $this->faker->imageUrl(800, 600, 'venue'),
                'gallery' => [
                    $this->faker->imageUrl(800, 600, 'venue'),
                    $this->faker->imageUrl(800, 600, 'venue'),
                    $this->faker->imageUrl(800, 600, 'venue'),
                ],
            ],
            'amenities' => [
                'parking' => $this->faker->boolean(80),
                'wifi' => $this->faker->boolean(90),
                'lockers' => $this->faker->boolean(70),
                'showers' => $this->faker->boolean(60),
                'cafe' => $this->faker->boolean(40),
                'accessibility' => [
                    'wheelchair' => $this->faker->boolean(90),
                    'elevator' => $this->faker->boolean(80),
                    'parking' => $this->faker->boolean(85),
                ],
                'equipment' => $this->faker->words(5, true),
            ],
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the venue is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Configure the venue for a single-location business.
     */
    public function forSingleLocation(Business $business): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business->id,
            'name' => $business->name,
            'slug' => $business->slug,
            'description' => $business->description,
            'contact_info' => $business->contact_info,
            'business_hours' => $business->business_hours,
        ]);
    }

    /**
     * Configure the venue for a multi-location business.
     */
    public function forMultiLocation(Business $business): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business->id,
        ]);
    }
} 