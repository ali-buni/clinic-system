<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'ar_name',
        'en_name',
        'generic_name_ar',
        'generic_name_en',
        'strength',
        'form',
        'api_medicine_id',
        'is_custom'
    ];

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(Prescription_item::class, 'medicine_id', 'id');
    }
}
