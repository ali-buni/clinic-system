<?php

namespace App\Models;

use Database\Factories\PatientInfoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): Factory
    {
        return PatientInfoFactory::new();
    }

    protected $table = 'patient_infos';

    protected $fillable = [
        'user_id',
        'clinic_id',
        'nationality',
        'address',
        'marital_status',
        'emergency_phone',
        'allergies',
        'chronic_conditions',
        'career',
        'blood_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Patient_record::class, 'patient_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id');
    }
}
