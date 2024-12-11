<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activities = [
            'Training', 'Class', 'Session', 'Lesson', 'Workshop', 'Course',
            'Practice', 'Coaching', 'Program', 'Assessment'
        ];

        $sports = [
            'Personal', 'Group', 'Yoga', 'Pilates', 'Tennis', 'Swimming',
            'Basketball', 'Dance', 'Martial Arts', 'Golf', 'Fitness',
            'CrossFit', 'Boxing', 'Cycling', 'HIIT', 'Stretching'
        ];

        $name = $this->faker->randomElement($sports) . ' ' . $this->faker->randomElement($activities);

        // Random duration between 30 minutes and 3 hours, in 15-minute increments
        $duration = $this->faker->randomElement([30, 45, 60, 75, 90, 120, 150, 180]);
        
        return [
            'business_id' => Business::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'description' => $this->faker->paragraph(),
            'duration' => $duration,
            'price' => $this->faker->randomFloat(2, 20, 200),
            'capacity' => $this->faker->randomElement([1, 2, 4, 6, 8, 10, 15, 20]),
            'images' => [
                'main' => $this->faker->imageUrl(800, 600, 'sports'),
                'gallery' => [
                    $this->faker->imageUrl(800, 600, 'sports'),
                    $this->faker->imageUrl(800, 600, 'sports'),
                ],
            ],
            'settings' => [
                'skill_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced', 'all']),
                'age_group' => $this->faker->randomElement(['kids', 'teens', 'adults', 'seniors', 'all']),
                'equipment_provided' => $this->faker->boolean(80),
                'requirements' => [
                    'minimum_age' => $this->faker->optional()->numberBetween(5, 18),
                    'fitness_level' => $this->faker->optional()->randomElement(['low', 'moderate', 'high']),
                    'prerequisites' => $this->faker->optional()->words(3, true),
                ],
                'cancellation_policy' => [
                    'deadline_hours' => $this->faker->randomElement([24, 48, 72]),
                    'refund_percentage' => $this->faker->randomElement([0, 50, 100]),
                ],
            ],
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the service is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Configure the service for a specific business.
     */
    public function forBusiness(Business $business): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business->id,
        ]);
    }

    /**
     * Configure the service for a specific category.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Configure the service as a private session (capacity = 1).
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => 1,
            'settings' => array_merge($attributes['settings'] ?? [], [
                'session_type' => 'private',
            ]),
        ]);
    }

    /**
     * Configure the service as a group session.
     */
    public function group(int $capacity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity ?? $this->faker->randomElement([4, 6, 8, 10, 15, 20]),
            'settings' => array_merge($attributes['settings'] ?? [], [
                'session_type' => 'group',
            ]),
        ]);
    }
} 