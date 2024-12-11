<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'venue_id',
        'service_id',
        'staff_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked',
        'is_available',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
        'capacity' => 'integer',
        'booked' => 'integer',
    ];

    /**
     * Get the business that owns the time slot.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the venue that owns the time slot.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the service that owns the time slot.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member assigned to the time slot.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Check if the time slot is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_available && 
            $this->status === 'available' && 
            $this->booked < $this->capacity;
    }

    /**
     * Get the remaining capacity.
     */
    public function getRemainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked);
    }

    /**
     * Scope a query to only include available time slots.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true)
            ->where('status', 'available')
            ->whereRaw('booked < capacity');
    }

    /**
     * Scope a query to only include future time slots.
     */
    public function scopeFuture(Builder $query): void
    {
        $query->whereDate('date', '>=', now()->toDateString())
            ->orWhere(function ($query) {
                $query->whereDate('date', '=', now()->toDateString())
                    ->whereTime('start_time', '>', now()->toTimeString());
            });
    }

    /**
     * Scope a query to only include time slots for a specific date range.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'fully-booked' => 'Fully Booked',
            'blocked' => 'Blocked',
            default => ucfirst($this->status),
        };
    }
}
