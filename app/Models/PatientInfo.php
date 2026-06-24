<?php

namespace App\Models;

use Database\Factories\PatientInfoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;

class PatientInfo extends Model
{
    use HasFactory, SoftDeletes, UsesCipherSweet;

    protected $table = 'patient_infos';

    public static function getEncryptedColumns(): array
    {
        return ['nationality', 'address', 'emergency_phone', 'allergies', 'chronic_conditions', 'blood_type'];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('nationality')
            ->addField('address')
            ->addField('emergency_phone')
            ->addField('allergies')
            ->addField('chronic_conditions')
            ->addField('blood_type')
            ->addBlindIndex('emergency_phone', new BlindIndex('emergency_phone_index'))
            ->addBlindIndex('nationality', new BlindIndex('nationality_index'));
    }

    public function scopeByEmergencyPhone(Builder $query, string $phone): Builder
    {
        return $query->whereBlind('emergency_phone', 'emergency_phone_index', $phone);
    }

    public function scopeByNationality(Builder $query, string $nationality): Builder
    {
        return $query->whereBlind('nationality', 'nationality_index', $nationality);
    }

    protected static function newFactory(): Factory
    {
        return PatientInfoFactory::new();
    }

    protected $fillable = [
        'user_id',
        'clinic_id',
        'nationality',
        'address',
        'marital_status',
        'emergency_phone',
        'allergies',
        'chronic_conditions',
        'career',
        'blood_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Patient_record::class, 'patient_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id');
    }
}
