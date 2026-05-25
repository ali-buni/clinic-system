<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'location',
        'title',
        'phone',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'clinic_id', 'id');
    }
    public function secretaries(): HasMany
    {
        return $this->hasMany(Secretary::class, 'clinic_id', 'id');
    }
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'clinic_id', 'id');
    }
    public function patientRecords(): HasMany
    {
        return $this->hasMany(Patient_record::class, 'clinic_id', 'id');
    }
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'id');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'clinic_id', 'id');
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'clinic_id', 'id');
    }
}
