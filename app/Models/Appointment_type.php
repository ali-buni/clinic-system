<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment_type extends Model
{
    protected $fillable = ['ar_name', 'en_name'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
