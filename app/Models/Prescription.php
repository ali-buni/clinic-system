<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_record_id',
        'doctor_id',
        'valid_until',
        'issued_at',
        'cost',
        'notes'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
    public function patientRecord(): BelongsTo
    {
        return $this->belongsTo(Patient_record::class, 'patient_record_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Prescription_item::class);
    }
}
