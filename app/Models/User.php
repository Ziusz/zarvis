<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'specialties',
        'experience',
        'languages',
        'average_rating',
        'reviews_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'specialties' => 'array',
        'languages' => 'array',
        'average_rating' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the businesses owned by the user.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    /**
     * Get the businesses where the user is a staff member.
     */
    public function staffBusinesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_staff')
            ->withPivot(['role', 'specialties', 'experience', 'languages', 'status'])
            ->withTimestamps();
    }

    /**
     * Get the staff availabilities for the user.
     */
    public function staffAvailabilities()
    {
        return $this->hasMany(StaffAvailability::class);
    }

    /**
     * Get the services that the staff member can provide.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->withPivot(['venue_id', 'settings', 'status'])
            ->withTimestamps();
    }

    /**
     * Get the bookings where the user is the staff member.
     */
    public function staffBookings()
    {
        return $this->hasMany(Booking::class, 'staff_id');
    }

    /**
     * Get the bookings made by the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the user is a staff member.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if the user is a business owner.
     */
    public function isBusinessOwner(): bool
    {
        return $this->businesses()->exists();
    }

    /**
     * Get the next available time slot for the staff member.
     */
    public function getNextAvailableAttribute()
    {
        $nextAvailable = $this->staffAvailabilities()
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('is_available', true)
            ->where('status', 'available')
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        if (!$nextAvailable) {
            return null;
        }

        return $nextAvailable->date->format('M j') . ' at ' . 
            Carbon::parse($nextAvailable->start_time)->format('g:i A');
    }
}
