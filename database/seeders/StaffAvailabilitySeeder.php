<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use App\Models\StaffAvailability;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class StaffAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all staff members
        $staffMembers = User::where('role', 'staff')->get();
        
        // Get all businesses with their venues and services
        $businesses = Business::with(['venues', 'services'])->get();

        // Generate availabilities for the next 14 days
        $dates = collect(CarbonPeriod::create(
            now()->startOfDay(),
            now()->addDays(14)->endOfDay()
        ))->map(fn ($date) => $date->format('Y-m-d'));

        foreach ($businesses as $business) {
            // Get business hours
            $businessHours = $business->business_hours;

            // Assign random staff to each venue
            foreach ($business->venues as $venue) {
                // Calculate how many staff to assign (min 1, max 3 or total staff count)
                $staffCount = min(rand(1, 3), $staffMembers->count());
                
                // Assign staff members to venue
                $venueStaff = $staffMembers->random($staffCount);

                foreach ($venueStaff as $staff) {
                    // Calculate how many services to assign (min 1, max 3 or total services count)
                    $serviceCount = min(rand(1, 3), $business->services->count());
                    
                    // Assign random services to staff
                    $staffServices = $business->services->random($serviceCount);
                    
                    $staff->services()->attach($staffServices->pluck('id'), [
                        'venue_id' => $venue->id,
                        'status' => 'active',
                    ]);

                    // Generate availabilities for each date
                    foreach ($dates as $date) {
                        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
                        
                        // Skip if business is closed on this day
                        if (!isset($businessHours[$dayOfWeek])) {
                            continue;
                        }

                        // 80% chance of staff being available on any given day
                        if (rand(1, 100) <= 80) {
                            $startTime = Carbon::parse($businessHours[$dayOfWeek][0]);
                            $endTime = Carbon::parse($businessHours[$dayOfWeek][1]);

                            // Create staff availability
                            StaffAvailability::create([
                                'user_id' => $staff->id,
                                'business_id' => $business->id,
                                'venue_id' => $venue->id,
                                'date' => $date,
                                'start_time' => $startTime->format('H:i:s'),
                                'end_time' => $endTime->format('H:i:s'),
                                'is_available' => true,
                                'status' => 'available',
                            ]);

                            // Generate time slots for each service
                            foreach ($staffServices as $service) {
                                $slotStart = clone $startTime;
                                
                                while ($slotStart->copy()->addMinutes($service->duration) <= $endTime) {
                                    TimeSlot::create([
                                        'business_id' => $business->id,
                                        'venue_id' => $venue->id,
                                        'service_id' => $service->id,
                                        'staff_id' => $staff->id,
                                        'date' => $date,
                                        'start_time' => $slotStart->format('H:i:s'),
                                        'end_time' => $slotStart->copy()->addMinutes($service->duration)->format('H:i:s'),
                                        'capacity' => $service->capacity,
                                        'booked' => 0,
                                        'is_available' => true,
                                        'status' => 'available',
                                    ]);

                                    $slotStart->addMinutes($service->duration);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
} 