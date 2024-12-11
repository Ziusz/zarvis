<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StaffAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'venue_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
    ];

    /**
     * Get the staff member that owns the availability.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the business that owns the availability.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the venue that owns the availability.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Check if the staff member is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_available && $this->status === 'available';
    }

    /**
     * Scope a query to only include available staff.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true)
            ->where('status', 'available');
    }

    /**
     * Scope a query to only include future availabilities.
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
     * Scope a query to only include availabilities for a specific date range.
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
            'unavailable' => 'Unavailable',
            'on-leave' => 'On Leave',
            default => ucfirst($this->status),
        };
    }
}
