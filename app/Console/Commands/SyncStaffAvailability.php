<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\StaffAvailability;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncStaffAvailability extends Command
{
    protected $signature = 'staff:sync-availability {--business_id= : The ID of the business to sync} {--days=30 : Number of days to sync ahead}';
    protected $description = 'Sync staff availability with business opening hours';

    public function handle()
    {
        $businessId = $this->option('business_id');
        $daysAhead = $this->option('days');

        $query = Business::query();
        if ($businessId) {
            $query->where('id', $businessId);
        }

        $businesses = $query->with(['staffMembers', 'primaryVenue'])->get();

        foreach ($businesses as $business) {
            $this->info("Syncing availability for {$business->name}");
            $this->syncBusinessStaffAvailability($business, $daysAhead);
        }

        $this->info('Staff availability sync completed!');
    }

    protected function syncBusinessStaffAvailability(Business $business, int $daysAhead)
    {
        $startDate = now()->startOfDay();
        $endDate = now()->addDays($daysAhead)->endOfDay();

        // Get opening hours
        $opening_hours = $business->opening_hours;
        if (is_string($opening_hours)) {
            try {
                $opening_hours = json_decode($opening_hours, true);
            } catch (\Exception $e) {
                $this->error("Failed to parse opening hours for business {$business->id}");
                return;
            }
        }

        foreach ($business->staffMembers as $staff) {
            $this->info("Processing staff member: {$staff->name}");
            
            // Loop through each day
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayOfWeek = strtolower($date->format('l'));
                $dayHours = $opening_hours[$dayOfWeek] ?? null;

                // Check if business is open
                $isOpen = false;
                $startTime = null;
                $endTime = null;

                if ($dayHours) {
                    if (isset($dayHours['is_open'])) {
                        // New format
                        $isOpen = $dayHours['is_open'];
                        $startTime = $dayHours['start'];
                        $endTime = $dayHours['end'];
                    } else {
                        // Old format
                        $isOpen = !($dayHours['closed'] ?? true);
                        $startTime = $dayHours['open'] ?? null;
                        $endTime = $dayHours['close'] ?? null;
                    }
                }

                if ($isOpen && $startTime && $endTime) {
                    // Create or update availability
                    StaffAvailability::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'user_id' => $staff->id,
                            'venue_id' => $business->primaryVenue->id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'is_available' => true,
                            'status' => 'available',
                        ]
                    );
                }
            }
        }
    }
} 