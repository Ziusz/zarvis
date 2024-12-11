<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get customers
        $customers = User::where('role', 'customer')->get();

        // Get available time slots
        $timeSlots = TimeSlot::where('date', '>=', now()->subDays(30))
            ->where('is_available', true)
            ->where('status', 'available')
            ->with(['service', 'business'])
            ->get();

        // Generate 100 random bookings
        for ($i = 0; $i < 100; $i++) {
            $customer = $customers->random();
            $timeSlot = $timeSlots->random();
            $service = $timeSlot->service;

            // Determine booking status based on date
            $status = 'pending';
            $paymentStatus = 'pending';
            $slotDate = Carbon::parse($timeSlot->date);

            if ($slotDate->isPast()) {
                // Past bookings
                if (rand(1, 100) <= 90) {
                    // 90% completed
                    $status = 'completed';
                    $paymentStatus = 'paid';
                } else {
                    // 10% no-show
                    $status = 'no-show';
                    $paymentStatus = 'pending';
                }
            } elseif ($slotDate->isToday()) {
                // Today's bookings
                $slotTime = Carbon::parse($timeSlot->start_time);
                if ($slotTime->isPast()) {
                    $status = rand(1, 100) <= 95 ? 'completed' : 'no-show';
                    $paymentStatus = $status === 'completed' ? 'paid' : 'pending';
                } else {
                    $status = 'confirmed';
                    $paymentStatus = rand(1, 100) <= 70 ? 'paid' : 'pending';
                }
            } else {
                // Future bookings
                $status = rand(1, 100) <= 80 ? 'confirmed' : 'pending';
                $paymentStatus = rand(1, 100) <= 60 ? 'paid' : 'pending';
            }

            // Random number of participants (1 to service capacity)
            $participants = rand(1, min($service->capacity, $timeSlot->capacity - $timeSlot->booked));

            // Skip if no capacity left
            if ($participants < 1) {
                continue;
            }

            DB::beginTransaction();
            try {
                // Create booking
                Booking::create([
                    'user_id' => $customer->id,
                    'business_id' => $timeSlot->business_id,
                    'venue_id' => $timeSlot->venue_id,
                    'service_id' => $timeSlot->service_id,
                    'staff_id' => $timeSlot->staff_id,
                    'start_time' => Carbon::parse($timeSlot->date)->setTimeFromTimeString($timeSlot->start_time),
                    'end_time' => Carbon::parse($timeSlot->date)->setTimeFromTimeString($timeSlot->end_time),
                    'participants' => $participants,
                    'total_price' => $service->price * $participants,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentStatus === 'paid' ? $this->getRandomPaymentMethod() : null,
                    'notes' => rand(1, 100) <= 30 ? $this->getRandomNote() : null,
                    'customer_details' => [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                    'service_details' => [
                        'name' => $service->name,
                        'duration' => $service->duration,
                        'price' => $service->price,
                    ],
                    'confirmed_at' => in_array($status, ['confirmed', 'completed']) ? now() : null,
                    'completed_at' => $status === 'completed' ? now() : null,
                ]);

                // Update time slot
                $newBookedCount = $timeSlot->booked + $participants;
                $timeSlot->booked = $newBookedCount;
                $timeSlot->status = $newBookedCount >= $timeSlot->capacity ? 'fully-booked' : 'available';
                $timeSlot->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }

    /**
     * Get a random payment method.
     */
    private function getRandomPaymentMethod(): string
    {
        return collect([
            'credit_card',
            'debit_card',
            'paypal',
            'apple_pay',
            'google_pay',
        ])->random();
    }

    /**
     * Get a random booking note.
     */
    private function getRandomNote(): string
    {
        return collect([
            'Please bring your own equipment.',
            'First time trying this activity.',
            'Need parking information.',
            'Prefer female instructor if possible.',
            'Have a minor knee injury, need modified exercises.',
            'Will be 5 minutes late.',
            'Bringing a friend to watch.',
            'Need locker access.',
            'Allergic to latex.',
            'Previous experience with similar activities.',
        ])->random();
    }
} 