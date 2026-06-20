<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'phone',
        'password',
        'dob',
        'gender',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function clinicOwner(): HasOne
    {
        return $this->hasOne(Clinic::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientInfo::class, 'user_id');
    }
    
    public function secretaryProfile(): HasOne
    {
        return $this->hasOne(Secretary::class);
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(Verification_code::class);
    }

    /**
     * Scope: Find user by phone number.
     */
    public function scopeByPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone', $phone);
    }

    /**
     * Scope: Find user by email.
     */
    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }
}
