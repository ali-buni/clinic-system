<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'country',
        'governorate',
        'city',
    ];

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class, 'location_id', 'id');
    }

    public function makeLocation(): string
    {
        return $this->country . ', ' . $this->governorate . ', ' . $this->city . ', ' . $this->name;
    }
}
