<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business for the booking.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the venue for the booking.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the service for the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member assigned to the booking.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Check if the booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            $this->start_time->isFuture();
    }

    /**
     * Check if the booking can be rescheduled.
     */
    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            $this->start_time->isFuture();
    }

    /**
     * Get the duration of the booking in minutes.
     */
    public function getDurationInMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
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

    /**
     * Get the payment status label.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pending',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
            default => ucfirst($this->payment_status),
        };
    }
}
