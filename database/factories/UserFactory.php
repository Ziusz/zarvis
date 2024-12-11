<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
            'role' => 'customer',
            'phone' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user is a staff member.
     */
    public function staff(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'staff',
                'specialties' => $this->faker->randomElements([
                    'Personal Training',
                    'Group Fitness',
                    'Yoga',
                    'Pilates',
                    'CrossFit',
                    'Swimming',
                    'Tennis',
                    'Basketball',
                    'Soccer',
                    'Martial Arts',
                ], rand(2, 4)),
                'experience' => $this->faker->randomElement([
                    'Over 5 years of experience in fitness training',
                    'Certified personal trainer with expertise in strength training',
                    'Former professional athlete with coaching experience',
                    'Specialized in rehabilitation and injury prevention',
                    'Expert in functional training and mobility work',
                ]),
                'languages' => $this->faker->randomElements([
                    'English',
                    'Spanish',
                    'French',
                    'German',
                    'Chinese',
                    'Japanese',
                    'Korean',
                    'Arabic',
                ], rand(1, 3)),
                'average_rating' => $this->faker->randomFloat(2, 3.5, 5.0),
                'reviews_count' => $this->faker->numberBetween(0, 100),
            ];
        });
    }
}
