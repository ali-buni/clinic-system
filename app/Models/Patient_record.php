<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;

class Patient_record extends Model
{
    use HasFactory, SoftDeletes, UsesCipherSweet;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'diagnosis_summary',
        'description',
        'status',
        'notes',
    ];

    public static function getEncryptedColumns(): array
    {
        return ['diagnosis_summary', 'description', 'notes'];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('diagnosis_summary')
            ->addField('description')
            ->addField('notes');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id');
    }
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'patient_record_disease')
            ->withPivot([
                'status',
                'severity',
            ]);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_record_id', 'id');
    }
}
