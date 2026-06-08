<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    public function secretaries(): BelongsToMany
    {
        return $this->belongsToMany(Secretary::class, 'room_secretary', 'room_id', 'secretary_id')->withTimestamps();
    }
}
