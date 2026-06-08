<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Secretary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'clinic_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_secretary', 'secretary_id', 'room_id')->withTimestamps();
    }
}
