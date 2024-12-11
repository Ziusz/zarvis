<?php

namespace Database\Factories;

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
        $name = fake()->company();
        
        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'logo' => fake()->imageUrl(200, 200, 'business'),
            'cover_image' => fake()->imageUrl(1200, 400, 'business'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'is_single_location' => fake()->boolean(70), // 70% chance of single location
            'status' => 'active',
            'business_hours' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['10:00', '16:00'],
                'sunday' => null,
            ],
            'settings' => [
                'booking_advance_days' => fake()->numberBetween(1, 30),
                'cancellation_policy' => '48h',
                'notification_preferences' => [
                    'email' => true,
                    'sms' => true,
                    'push' => true,
                ],
            ],
            'social_links' => [
                'facebook' => fake()->url(),
                'instagram' => fake()->url(),
                'twitter' => fake()->url(),
            ],
        ];
    }
} 