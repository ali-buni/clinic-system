<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'clinic_id',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'room_id', 'id');
    }
    public function secretaries(): HasMany
    {
        return $this->hasMany(Secretary::class, 'room_id', 'id');
    }
}
