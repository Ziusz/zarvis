<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

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
        'status',
        'is_verified',
        'settings',
        'business_hours',
        'social_links',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'settings' => 'array',
        'business_hours' => 'array',
        'social_links' => 'array',
    ];

    /**
     * Get all venues for the business.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    /**
     * Get the primary venue for the business.
     */
    public function primaryVenue(): HasOne
    {
        return $this->hasOne(Venue::class)->where('is_primary', true);
    }

    /**
     * Get all services for the business.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the categories for the business.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the business's address through the primary venue.
     */
    public function getAddressAttribute()
    {
        return $this->primaryVenue?->full_address;
    }

    /**
     * Check if the business is verified.
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
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
        $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include businesses in a specific category.
     */
    public function scopeInCategory(Builder $query, $category): void
    {
        $query->whereHas('categories', function ($query) use ($category) {
            $query->where('categories.id', $category instanceof Category ? $category->id : $category);
        });
    }

    /**
     * Scope a query to search businesses.
     */
    public function scopeSearch(Builder $query, string $search): void
    {
        $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('categories', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('services', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
        });
    }
} 