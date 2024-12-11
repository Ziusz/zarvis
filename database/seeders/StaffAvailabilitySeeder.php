<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\StaffAvailability;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StaffAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::with(['venues', 'staffMembers'])->get();

        foreach ($businesses as $business) {
            // Get the primary venue
            $venue = $business->primaryVenue;
            if (!$venue) continue;

            // Get all staff members
            $staffMembers = $business->staffMembers;
            if ($staffMembers->isEmpty()) continue;

            // Generate availabilities for the next 30 days
            for ($day = 0; $day < 30; $day++) {
                $date = Carbon::today()->addDays($day);

                // Skip if it's a weekend
                if ($date->isWeekend()) continue;

                foreach ($staffMembers as $staff) {
                    // 80% chance of being available
                    if (rand(1, 100) <= 80) {
                        // Create full day availability (9 AM to 5 PM)
                        StaffAvailability::create([
                            'user_id' => $staff->id,
                            'business_id' => $business->id,
                            'venue_id' => $venue->id,
                            'date' => $date->toDateString(),
                            'start_time' => $date->copy()->setHour(9)->setMinute(0),
                            'end_time' => $date->copy()->setHour(17)->setMinute(0),
                            'is_available' => true,
                            'status' => 'available',
                            'notes' => 'Regular working hours',
                        ]);
                    }
                }
            }
        }
    }
} 