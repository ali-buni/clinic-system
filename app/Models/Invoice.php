<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'appointment_id',
        'invoice_number',
        'status',
        'total_cost',
        'description',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id', 'id');
    }
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'invoice_items', 'invoice_id', 'item_id')
            ->withPivot('item_name', 'price', 'quantity')
            ->withTimestamps();
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
