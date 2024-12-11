<?php

namespace Database\Factories;

use App\Models\Business;
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
        $name = fake()->city();
        
        return [
            'business_id' => Business::factory(),
            'name' => $name,
            'slug' => Str::slug($name . '-' . Str::random(6)),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'contact_info' => [
                'phone' => fake()->phoneNumber(),
                'email' => fake()->email(),
                'manager' => fake()->name(),
            ],
            'business_hours' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['10:00', '16:00'],
                'sunday' => null,
            ],
            'images' => [
                'main' => fake()->imageUrl(800, 600, 'venue'),
                'gallery' => [
                    fake()->imageUrl(800, 600, 'venue'),
                    fake()->imageUrl(800, 600, 'venue'),
                    fake()->imageUrl(800, 600, 'venue'),
                ],
            ],
            'amenities' => [
                'parking' => fake()->boolean(),
                'wifi' => fake()->boolean(),
                'lockers' => fake()->boolean(),
                'showers' => fake()->boolean(),
                'cafe' => fake()->boolean(),
                'accessibility' => [
                    'wheelchair' => fake()->boolean(),
                    'elevator' => fake()->boolean(),
                    'parking' => fake()->boolean(),
                ],
                'equipment' => fake()->text(40),
            ],
            'status' => fake()->randomElement(['active', 'inactive']),
            'is_primary' => false,
        ];
    }
} 