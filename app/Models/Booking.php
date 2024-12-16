<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_id',
        'venue_id',
        'service_id',
        'staff_id',
        'start_time',
        'end_time',
        'participants',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'payment_id',
        'customer_details',
        'service_details',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'customer_details' => 'array',
        'service_details' => 'array',
        'total_price' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeCancelled()
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        // Can't cancel if less than 24 hours before start time
        return $this->start_time->diffInHours(now()) >= 24;
    }

    public function canBeRescheduled()
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        // Can't reschedule if less than 24 hours before start time
        return $this->start_time->diffInHours(now()) >= 24;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no-show' => 'No Show',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'Pending',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
            default => ucfirst($this->payment_status),
        };
    }

    public function getDurationInMinutes()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
}
