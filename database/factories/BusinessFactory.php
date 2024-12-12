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
    protected $model = Business::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        
        // Polish cities
        $cities = [
            'Warszawa', 'Kraków', 'Łódź', 'Wrocław', 'Poznań', 
            'Gdańsk', 'Szczecin', 'Katowice', 'Lublin', 'Białystok'
        ];

        // Polish street names
        $streets = [
            'Marszałkowska', 'Piotrkowska', 'Świętokrzyska', 'Długa', 'Floriańska',
            'Mickiewicza', 'Kościuszki', 'Sienkiewicza', 'Słowackiego', 'Piłsudskiego'
        ];

        // Generate a valid Polish NIP
        $generateNIP = function() {
            $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
            $digits = array_map(fn() => rand(0, 9), range(1, 9));
            $sum = 0;
            
            for ($i = 0; $i < 9; $i++) {
                $sum += $digits[$i] * $weights[$i];
            }
            
            $checksum = $sum % 11;
            if ($checksum === 10) {
                $checksum = 0;
            }
            
            $digits[] = $checksum;
            return implode('', $digits);
        };

        $city = $this->faker->randomElement($cities);
        $street = $this->faker->randomElement($streets);
        $buildingNumber = $this->faker->buildingNumber();
        // Polish postal code format: XX-XXX
        $postalCode = sprintf("%02d-%03d", rand(0, 99), rand(0, 999));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'street_address' => "ul. {$street} {$buildingNumber}",
            'city' => $city,
            'postal_code' => $postalCode,
            'nip' => $generateNIP(),
            'phone' => $this->faker->regexify('(?:(?:(?:\+|00)?48)|(?:\(\+?48\)))?(?:1[2-8]|2[2-69]|3[2-49]|4[1-8]|5[0-9]|6[0-35-9]|[7-8][1-9]|9[145])\d{7}'),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'owner_id' => User::factory(),
            'opening_hours' => [
                'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'saturday' => ['open' => '10:00', 'close' => '14:00', 'closed' => false],
                'sunday' => ['open' => '10:00', 'close' => '14:00', 'closed' => true],
            ],
        ];
    }

    /**
     * Indicate that the business is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterCreating(function (Business $business) {
            // Attach the owner as staff
            $business->staffMembers()->attach($business->owner_id, [
                'role' => 'owner',
                'status' => 'active',
            ]);
        });
    }
} 