<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StaffAvailability extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'venue_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'status',
        'notes',
        'settings',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_available' => 'boolean',
        'settings' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function isAvailableForTimeSlot(TimeSlot $timeSlot): bool
    {
        if (!$this->is_available || $this->status !== 'available') {
            return false;
        }

        $slotStart = Carbon::parse($this->date->format('Y-m-d') . ' ' . $timeSlot->start_time);
        $slotEnd = Carbon::parse($this->date->format('Y-m-d') . ' ' . $timeSlot->end_time);

        return $this->start_time->lte($slotStart) && $this->end_time->gte($slotEnd);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                    ->where('status', 'available');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForVenue($query, $venueId)
    {
        return $query->where('venue_id', $venueId);
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('user_id', $staffId);
    }

    public function scopeOverlapping($query, $date, $startTime, $endTime)
    {
        return $query->where('date', $date)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                              ->where('end_time', '>', $startTime);
                        })->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<', $endTime)
                              ->where('end_time', '>=', $endTime);
                        })->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '>=', $startTime)
                              ->where('end_time', '<=', $endTime);
                        });
                    });
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'unavailable' => 'Unavailable',
            'on-leave' => 'On Leave',
            default => ucfirst($this->status),
        };
    }
}
