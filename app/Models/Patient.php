<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'fname',
        'lname',
        'dob',
        'gender',
        'phone',
        'nationality',
        'address',
        'marital_status',
        'emergency_phone',
        'allergies',
        'chronic_conditions',
        'career',
        'blood_type',
    ];

    protected $casts = ['dob' => 'date'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
    public function records(): HasMany
    {
        return $this->hasMany(Patient_record::class);
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
