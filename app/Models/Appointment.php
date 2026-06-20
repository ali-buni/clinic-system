<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'room_id',
        'patient_id',
        'appointment_type_id',
        'start_time',
        'end_time',
        'status',
        'cancel_reason',
        'visit_reason',
        'visit_in_time',
        'requires_followup',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * scope for the scheduled appointment in day
     */
    public function scopeScheduledInDate(Builder $query, int $doctorId, string $date): Builder
    {
        return $query->where('doctor_id', $doctorId)->whereDate('start_time', $date)
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show']);
    }

    /**
     * scope for the all valid appointments in day
     */
    public function scopeAllValidInDate(Builder $query, int $doctorId, string $date): Builder
    {
        return $query->where('doctor_id', $doctorId)->whereDate('start_time', $date)
            ->whereNotIn('status', ['cancelled', 'no_show']);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Appointment_type::class, 'appointment_type_id', 'id');
    }

    public function record(): HasOne
    {
        return $this->hasOne(Patient_record::class);
    }
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
