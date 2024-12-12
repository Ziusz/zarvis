<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'service_id',
        'staff_id',
        'venue_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'capacity' => 'integer',
        'booked' => 'integer',
    ];

    protected $attributes = [
        'booked' => 0,
        'status' => 'available',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where(function ($query) {
                $query->where('capacity', '>', DB::raw('booked'))
                    ->orWhereNull('booked');
            });
    }

    public function isAvailable()
    {
        return $this->status === 'available' && 
            ($this->booked < $this->capacity || is_null($this->booked));
    }

    public function getRemainingCapacity()
    {
        if (is_null($this->booked)) {
            return $this->capacity;
        }
        return max(0, $this->capacity - $this->booked);
    }
}
