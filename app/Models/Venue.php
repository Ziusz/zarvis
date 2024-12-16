<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'contact_info',
        'business_hours',
        'images',
        'amenities',
        'status',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contact_info' => 'array',
        'business_hours' => 'array',
        'images' => 'array',
        'amenities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venue) {
            // Auto-generate slug if not provided
            if (empty($venue->slug)) {
                $venue->slug = $venue->business->is_single_location
                    ? $venue->business->slug
                    : Str::slug($venue->name);
            }

            // For single-location businesses, sync with business data
            if ($venue->business->is_single_location) {
                $venue->name ??= $venue->business->name;
                $venue->description ??= $venue->business->description;
                $venue->contact_info ??= $venue->business->contact_info;
                $venue->business_hours ??= $venue->business->business_hours;
            }
        });
    }

    /**
     * Get the business that owns the venue.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the services available at this venue.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_venue')
            ->withPivot(['price', 'duration', 'capacity', 'settings', 'status'])
            ->withTimestamps();
    }

    /**
     * Get active services available at this venue.
     */
    public function activeServices(): BelongsToMany
    {
        return $this->services()
            ->wherePivot('status', 'active')
            ->where('services.status', 'active');
    }

    /**
     * Get staff members assigned to this venue.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_staff')
            ->withPivot(['service_id', 'settings', 'status'])
            ->withTimestamps();
    }

    /**
     * Get active staff members who can provide services at this venue.
     */
    public function activeStaff(): BelongsToMany
    {
        return $this->staff()
            ->wherePivot('status', 'active');
    }

    /**
     * Get the venue's name (falls back to business name for single-location businesses).
     */
    public function getNameAttribute($value): string
    {
        if ($this->business->is_single_location) {
            return $value ?? $this->business->name;
        }
        return $value;
    }

    /**
     * Get the venue's description (falls back to business description for single-location businesses).
     */
    public function getDescriptionAttribute($value): ?string
    {
        if ($this->business->is_single_location) {
            return $value ?? $this->business->description;
        }
        return $value;
    }

    /**
     * Get the venue's contact info (falls back to business contact info for single-location businesses).
     */
    public function getContactInfoAttribute($value): ?array
    {
        if ($this->business->is_single_location) {
            $value = $value ? (is_string($value) ? json_decode($value, true) : $value) : null;
            $businessInfo = $this->business->contact_info;
            return $value ?? $businessInfo;
        }
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Get the venue's business hours (falls back to business hours for single-location businesses).
     */
    public function getBusinessHoursAttribute($value): ?array
    {
        if ($this->business->is_single_location) {
            $value = $value ? (is_string($value) ? json_decode($value, true) : $value) : null;
            $businessHours = $this->business->business_hours;
            return $value ?? $businessHours;
        }
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Get the venue's images.
     */
    public function getImagesAttribute($value): ?array
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Get the venue's amenities.
     */
    public function getAmenitiesAttribute($value): ?array
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Scope a query to only include active venues.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the venue is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the venue's full address with coordinates.
     */
    public function getFullAddressAttribute(): array
    {
        return [
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Get an amenity value.
     */
    public function getAmenity(string $key, $default = null)
    {
        return data_get($this->amenities, $key, $default);
    }

    /**
     * Set an amenity value.
     */
    public function setAmenity(string $key, $value): self
    {
        $amenities = $this->amenities ?? [];
        data_set($amenities, $key, $value);
        $this->amenities = $amenities;
        return $this;
    }
} 