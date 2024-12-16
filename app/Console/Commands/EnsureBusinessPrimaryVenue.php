<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

class EnsureBusinessPrimaryVenue extends Command
{
    protected $signature = 'business:ensure-primary-venue';
    protected $description = 'Ensure all businesses have a primary venue';

    public function handle()
    {
        $businesses = Business::doesntHave('primaryVenue')->get();
        $count = 0;

        foreach ($businesses as $business) {
            // Check if business has any venues
            $existingVenue = $business->venues()->first();

            if ($existingVenue) {
                // Make the first venue primary
                $existingVenue->update(['is_primary' => true]);
            } else {
                // Create a new primary venue
                $business->venues()->create([
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'description' => $business->description,
                    'address' => $business->full_address,
                    'contact_info' => [
                        'phone' => $business->phone,
                        'email' => $business->email,
                    ],
                    'business_hours' => $business->opening_hours,
                    'status' => 'active',
                    'is_primary' => true,
                ]);
            }

            $count++;
        }

        $this->info("Fixed {$count} businesses without primary venues.");
    }
} 