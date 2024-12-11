<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create test staff members
        User::factory()
            ->count(20)
            ->staff()
            ->create();

        // Create test customers
        User::factory()
            ->count(50)
            ->create();

        $this->call([
            CategorySeeder::class,
            BusinessSeeder::class,
            StaffAvailabilitySeeder::class,
            BookingSeeder::class,
        ]);
    }
}
