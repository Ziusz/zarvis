<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Service;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimeSlotService
{
    /**
     * Generate time slots for a specific date based on business opening hours
     */
    public function generateTimeSlotsForDate(Business $business, Service $service, string $date): Collection
    {
        // Check if business has a primary venue
        if (!$business->primaryVenue) {
            \Log::info('No primary venue found', ['business_id' => $business->id]);
            return collect([]);
        }

        $carbon_date = Carbon::parse($date);
        $day_of_week = strtolower($carbon_date->format('l'));
        
        // Handle cases where opening_hours might be null or a string
        $opening_hours = $business->opening_hours;
        if (is_string($opening_hours)) {
            try {
                $opening_hours = json_decode($opening_hours, true);
            } catch (\Exception $e) {
                \Log::error('Failed to parse opening hours', [
                    'business_id' => $business->id,
                    'opening_hours' => $opening_hours,
                    'error' => $e->getMessage()
                ]);
                return collect([]);
            }
        }

        \Log::info('Processing opening hours', [
            'business_id' => $business->id,
            'date' => $date,
            'day_of_week' => $day_of_week,
            'opening_hours' => $opening_hours,
        ]);

        // Get opening hours for the specific day
        $day_hours = $opening_hours[$day_of_week] ?? null;
        
        // If no hours set for this day
        if (!$day_hours) {
            \Log::info('No hours found for day', ['day' => $day_of_week]);
            return collect([]);
        }

        // Handle both old and new format
        $is_open = false;
        $start_time = null;
        $end_time = null;

        if (isset($day_hours['is_open'])) {
            // New format
            $is_open = $day_hours['is_open'];
            $start_time = $day_hours['start'];
            $end_time = $day_hours['end'];
        } else {
            // Old format
            $is_open = !($day_hours['closed'] ?? true);
            $start_time = $day_hours['open'] ?? null;
            $end_time = $day_hours['close'] ?? null;
        }

        \Log::info('Parsed business hours', [
            'is_open' => $is_open,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'day_hours' => $day_hours
        ]);

        // If business is closed or times not set
        if (!$is_open || !$start_time || !$end_time) {
            \Log::info('Business is closed or times not set', [
                'is_open' => $is_open,
                'start_time' => $start_time,
                'end_time' => $end_time
            ]);
            return collect([]);
        }

        try {
            // Create Carbon instances for start and end times
            $start_time = Carbon::parse($date . ' ' . $start_time);
            $end_time = Carbon::parse($date . ' ' . $end_time);
            $duration = $service->duration;

            \Log::info('Service duration and times', [
                'duration' => $duration,
                'start_time' => $start_time->format('Y-m-d H:i:s'),
                'end_time' => $end_time->format('Y-m-d H:i:s')
            ]);

            // If end time is less than start time, it means it spans to next day
            if ($end_time->lt($start_time)) {
                $end_time->addDay();
            }

            $slots = collect();
            $current_time = $start_time->copy();

            // Generate slots until we reach the end time
            while ($current_time->copy()->addMinutes($duration)->lte($end_time)) {
                $slot_start = $current_time->copy();
                $slot_end = $slot_start->copy()->addMinutes($duration);

                // Check if there's already a booking for this time slot
                $existing_slot = TimeSlot::where([
                    'business_id' => $business->id,
                    'service_id' => $service->id,
                    'date' => $date,
                    'start_time' => $slot_start->format('H:i:s'),
                    'end_time' => $slot_end->format('H:i:s'),
                ])->first();

                if (!$existing_slot) {
                    $slots->push([
                        'id' => "{$date}_{$slot_start->format('H:i:s')}_{$slot_end->format('H:i:s')}",
                        'business_id' => $business->id,
                        'service_id' => $service->id,
                        'venue_id' => $business->primaryVenue->id,
                        'date' => $date,
                        'start_time' => $slot_start->format('H:i:s'),
                        'end_time' => $slot_end->format('H:i:s'),
                        'capacity' => $service->capacity,
                        'booked' => 0,
                        'is_available' => true,
                        'status' => 'available',
                    ]);
                } else {
                    $slots->push($existing_slot);
                }

                // Move to next slot
                $current_time->addMinutes($duration);
            }

            \Log::info('Generated time slots', [
                'business_id' => $business->id,
                'service_id' => $service->id,
                'date' => $date,
                'day_of_week' => $day_of_week,
                'slots_count' => $slots->count(),
                'first_slot' => $slots->first(),
                'last_slot' => $slots->last(),
            ]);

            return $slots;
        } catch (\Exception $e) {
            \Log::error('Error generating time slots: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'service_id' => $service->id,
                'date' => $date,
                'opening_hours' => $day_hours,
                'exception' => $e->getTraceAsString(),
            ]);
            return collect([]);
        }
    }

    /**
     * Get available time slots for a specific date
     */
    public function getAvailableTimeSlots(Business $business, Service $service, string $date): Collection
    {
        $slots = $this->generateTimeSlotsForDate($business, $service, $date);

        return $slots->filter(function ($slot) {
            if (is_array($slot)) {
                return true; // New slots are always available
            }
            
            return $slot->is_available && $slot->booked < $slot->capacity;
        });
    }
} 