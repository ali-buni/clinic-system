<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disease extends Model
{
    protected $fillable = ['code', 'ar_name', 'en_name', 'description', 'disease_nature'];

    public function patientRecords(): BelongsToMany
    {
        return $this->belongsToMany(Patient_record::class, 'patient_record_disease')->withTimestamps();
    }
}
