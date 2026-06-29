<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicAnalyticsSnapshot extends Model
{
    protected $fillable = ['clinic_id', 'metric_name', 'data', 'snapshot_date'];

    protected $casts = [
        'data' => 'array', 
        'snapshot_date' => 'date',
    ];
}
