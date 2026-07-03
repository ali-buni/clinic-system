<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'clinic_id',
        'room_id',
        'appointment_duration',
        'bio',
        'consultation_fee',
        'stripe_connected_account_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty')->withTimestamps();
    }

    public function workHours(): HasMany
    {
        return $this->hasMany(Work_hour::class);
    }
    public function scheduleOverrides(): HasMany
    {
        return $this->hasMany(Schedule_override::class);
    }
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
    public function patientRecords(): HasMany
    {
        return $this->hasMany(Patient_record::class);
    }
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function wallet()
    {
        return $this->hasOne(DoctorWallet::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(DoctorWithdrawal::class);
    }

    public function getConsultationFeeAttribute(): ?float
    {
        return isset($this->attributes['consultation_fee'])
            ? (float) $this->attributes['consultation_fee']
            : null;
    }

    public function setConsultationFeeAttribute($value): void
    {
        $this->attributes['consultation_fee'] = $value;
    }
}
