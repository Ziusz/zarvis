<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'email',
        'phone',
        'website',
        'business_hours',
        'social_links',
        'settings',
        'verified_at',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'social_links' => 'array',
        'settings' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (!$business->slug) {
                $business->slug = Str::slug($business->name);
            }
        });
    }

    /**
     * Check if the business is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Scope a query to only include active businesses.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope a query to only include verified businesses.
     */
    public function scopeVerified(Builder $query): void
    {
        $query->whereNotNull('verified_at');
    }

    /**
     * Get the primary venue for the business.
     */
    public function primaryVenue(): HasOne
    {
        return $this->hasOne(Venue::class)->where('is_primary', true);
    }

    /**
     * Get all venues for the business.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    /**
     * Get all services for the business.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get all categories for the business.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get all staff members for the business.
     */
    public function staffMembers()
    {
        return $this->belongsToMany(User::class, 'business_staff')
            ->withPivot(['role', 'specialties', 'experience', 'languages'])
            ->withTimestamps();
    }

    /**
     * Get all bookings for the business.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all time slots for the business.
     */
    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    /**
     * Get all staff availabilities for the business.
     */
    public function staffAvailabilities(): HasMany
    {
        return $this->hasMany(StaffAvailability::class);
    }
} 