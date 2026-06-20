<?php

namespace App\Models;

use Database\Factories\AppointmentTypeFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment_type extends Model
{
    use HasFactory;

    protected $fillable = ['types', 'ar_name', 'en_name'];

    protected static function newFactory(): Factory
    {
        return AppointmentTypeFactory::new();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
