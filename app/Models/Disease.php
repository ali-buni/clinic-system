<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disease extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'ar_name',
        'en_name',
        'description',
        'disease_nature',
        'is_custom'
    ];

    public function patientRecords()
    {
        return $this->belongsToMany(Patient_record::class, 'patient_record_disease')
            ->withPivot([
                'status',
                'severity',
            ]);
    }
}
