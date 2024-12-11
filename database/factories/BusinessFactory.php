<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $businessTypes = ['Studio', 'Center', 'Club', 'Academy', 'School'];
        
        return [
            'user_id' => User::factory(),
            'name' => $name . ' ' . $this->faker->randomElement($businessTypes),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'logo' => $this->faker->imageUrl(200, 200, 'business'),
            'is_single_location' => $this->faker->boolean(70), // 70% chance of single location
            'contact_info' => [
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->companyEmail(),
                'website' => $this->faker->url(),
                'social' => [
                    'facebook' => 'https://facebook.com/' . $this->faker->userName(),
                    'instagram' => 'https://instagram.com/' . $this->faker->userName(),
                ],
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
            'settings' => [
                'booking_advance_days' => $this->faker->numberBetween(1, 30),
                'cancellation_policy' => $this->faker->randomElement(['24h', '48h', '72h']),
                'notification_preferences' => [
                    'email' => true,
                    'sms' => $this->faker->boolean(),
                    'push' => $this->faker->boolean(),
                ],
            ],
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended']),
            'verified_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }

    /**
     * Indicate that the business is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the business is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the business has a single location.
     */
    public function singleLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_single_location' => true,
        ]);
    }

    /**
     * Indicate that the business has multiple locations.
     */
    public function multiLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_single_location' => false,
        ]);
    }
} 