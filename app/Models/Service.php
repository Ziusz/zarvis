<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'slug',
        'description',
        'duration',
        'price',
        'capacity',
        'images',
        'settings',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'images' => 'array',
        'settings' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });

        // When creating a service for a single-location business,
        // automatically attach it to the primary venue
        static::created(function ($service) {
            if ($service->business->is_single_location) {
                $service->venues()->attach($service->business->primaryVenue->id, [
                    'price' => $service->price,
                    'duration' => $service->duration,
                    'capacity' => $service->capacity,
                    'status' => $service->status,
                ]);
            }
        });
    }

    /**
     * Get the business that owns the service.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the category of the service.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the venues where this service is available.
     */
    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'service_venue')
            ->withPivot(['price', 'duration', 'capacity', 'settings', 'status'])
            ->withTimestamps();
    }

    /**
     * Get the staff members who can provide this service.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_staff')
            ->withPivot(['venue_id', 'settings', 'status'])
            ->withTimestamps();
    }

    /**
     * Get the price for this service at a specific venue.
     */
    public function getPriceAtVenue(Venue $venue): float
    {
        $venueService = $this->venues()->where('venue_id', $venue->id)->first();
        return $venueService?->pivot?->price ?? $this->price;
    }

    /**
     * Get the duration for this service at a specific venue.
     */
    public function getDurationAtVenue(Venue $venue): int
    {
        $venueService = $this->venues()->where('venue_id', $venue->id)->first();
        return $venueService?->pivot?->duration ?? $this->duration;
    }

    /**
     * Get the capacity for this service at a specific venue.
     */
    public function getCapacityAtVenue(Venue $venue): int
    {
        $venueService = $this->venues()->where('venue_id', $venue->id)->first();
        return $venueService?->pivot?->capacity ?? $this->capacity;
    }

    /**
     * Get staff members who can provide this service at a specific venue.
     */
    public function getStaffAtVenue(Venue $venue)
    {
        return $this->staff()
            ->where(function ($query) use ($venue) {
                $query->where('service_staff.venue_id', $venue->id)
                    ->orWhereNull('service_staff.venue_id');
            })
            ->where('service_staff.status', 'active');
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): self
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        return $this;
    }

    /**
     * Format duration as human readable string.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ' . Str::plural('hour', $hours);
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . Str::plural('minute', $minutes);
        }

        return implode(' ', $parts);
    }

    /**
     * Get the service's images.
     */
    public function getImagesAttribute($value): ?array
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Get the service's settings.
     */
    public function getSettingsAttribute($value): ?array
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }
} 