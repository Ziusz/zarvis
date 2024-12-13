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
        'owner_id',
        'name',
        'slug',
        'description',
        'street_address',
        'city',
        'postal_code',
        'nip',
        'phone',
        'email',
        'website',
        'logo',
        'cover_image',
        'opening_hours',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'is_featured' => 'boolean',
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
        return $this->hasMany(Service::class)->orderBy('name');
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
            ->withPivot(['role', 'specialties', 'experience', 'languages', 'status'])
            ->withTimestamps()
            ->using(BusinessStaff::class);
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isStaffMember(User $user): bool
    {
        return $this->staffMembers()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->street_address}, {$this->postal_code} {$this->city}";
    }
} 