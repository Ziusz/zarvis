<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'is_single_location',
        'contact_info',
        'business_hours',
        'settings',
        'status',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_single_location' => 'boolean',
        'contact_info' => 'array',
        'business_hours' => 'array',
        'settings' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'verified_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (empty($business->slug)) {
                $business->slug = Str::slug($business->name);
            }
        });

        // When creating a single-location business, automatically create a venue
        static::created(function ($business) {
            if ($business->is_single_location) {
                $business->venues()->create([
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'description' => $business->description,
                    'contact_info' => $business->contact_info,
                    'business_hours' => $business->business_hours,
                ]);
            }
        });
    }

    /**
     * Get the owner of the business.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all venues for the business.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    /**
     * Get the primary venue for single-location businesses.
     */
    public function primaryVenue(): HasOne
    {
        return $this->hasOne(Venue::class)
            ->when($this->is_single_location, function ($query) {
                return $query->oldest();
            });
    }

    /**
     * Get all services offered by the business.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get active services offered by the business.
     */
    public function activeServices(): HasMany
    {
        return $this->services()->where('status', 'active');
    }

    /**
     * Get all staff members of the business.
     */
    public function staff(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            'service_staff',
            'business_id',
            'id',
            'id',
            'user_id'
        )->distinct();
    }

    /**
     * Get active staff members of the business.
     */
    public function activeStaff(): HasManyThrough
    {
        return $this->staff()
            ->where('service_staff.status', 'active');
    }

    /**
     * Get the business's address (for single-location businesses).
     */
    public function getAddressAttribute(): ?string
    {
        return $this->is_single_location 
            ? $this->primaryVenue?->address 
            : null;
    }

    /**
     * Get the business's coordinates (for single-location businesses).
     */
    public function getCoordinatesAttribute(): ?array
    {
        if (!$this->is_single_location || !$this->primaryVenue) {
            return null;
        }

        return [
            'latitude' => $this->primaryVenue->latitude,
            'longitude' => $this->primaryVenue->longitude,
        ];
    }

    /**
     * Scope a query to only include verified businesses.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope a query to only include active businesses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the business is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if the business is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
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
} 