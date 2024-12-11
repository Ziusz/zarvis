<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::with(['venues', 'services', 'staffMembers'])->get();

        foreach ($businesses as $business) {
            // Get the primary venue
            $venue = $business->primaryVenue;
            if (!$venue) continue;

            // Get all services
            $services = $business->services;
            if ($services->isEmpty()) continue;

            // Get all staff members
            $staffMembers = $business->staffMembers;

            // Generate time slots for the next 30 days
            for ($day = 0; $day < 30; $day++) {
                $date = Carbon::today()->addDays($day);

                // Skip if it's a weekend (you can customize this based on business hours)
                if ($date->isWeekend()) continue;

                // Generate slots from 9 AM to 5 PM
                $startHour = 9;
                $endHour = 17;

                for ($hour = $startHour; $hour < $endHour; $hour++) {
                    foreach ($services as $service) {
                        // Create a morning slot
                        TimeSlot::create([
                            'business_id' => $business->id,
                            'venue_id' => $venue->id,
                            'service_id' => $service->id,
                            'staff_id' => $staffMembers->isNotEmpty() ? $staffMembers->random()->id : null,
                            'date' => $date->toDateString(),
                            'start_time' => $date->copy()->setHour($hour)->setMinute(0),
                            'end_time' => $date->copy()->setHour($hour + 1)->setMinute(0),
                            'capacity' => $service->capacity ?? 1,
                            'booked' => 0,
                            'is_available' => true,
                            'status' => 'available',
                            'notes' => 'Generated time slot for testing',
                        ]);

                        // Create an afternoon slot
                        TimeSlot::create([
                            'business_id' => $business->id,
                            'venue_id' => $venue->id,
                            'service_id' => $service->id,
                            'staff_id' => $staffMembers->isNotEmpty() ? $staffMembers->random()->id : null,
                            'date' => $date->toDateString(),
                            'start_time' => $date->copy()->setHour($hour)->setMinute(30),
                            'end_time' => $date->copy()->setHour($hour + 1)->setMinute(30),
                            'capacity' => $service->capacity ?? 1,
                            'booked' => 0,
                            'is_available' => true,
                            'status' => 'available',
                            'notes' => 'Generated time slot for testing',
                        ]);
                    }
                }
            }
        }
    }
} 