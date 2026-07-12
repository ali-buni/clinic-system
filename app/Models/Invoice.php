<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Invoice extends Model implements CipherSweetEncrypted
{
    use HasFactory, SoftDeletes, UsesCipherSweet;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'appointment_id',
        'invoice_number',
        'status',
        'total_cost',
        'description',
    ];

    protected $casts = [];

    public static function getEncryptedColumns(): array
    {
        return ['invoice_number', 'total_cost', 'description'];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('invoice_number')
            ->addField('total_cost')
            ->addField('description')
            ->addBlindIndex('invoice_number', new BlindIndex('invoice_number_index'))
            ->addBlindIndex('description', new BlindIndex('description_index'));
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientInfo::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'invoice_items')
            ->withPivot('price', 'quantity')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function completedPayments(): HasMany
    {
        return $this->payments()->whereNotNull('paid_at');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getRemainingBalance(): float
    {
        $totalPaid = $this->completedPayments->sum('amount');

        return (float) $this->total_cost - (float) $totalPaid;
    }

    public function scopeByInvoiceNumber(Builder $query, string $invoiceNumber): Builder
    {
        return $query->whereBlind('invoice_number', 'invoice_number_index', $invoiceNumber);
    }

    public function scopeByDescription(Builder $query, string $description): Builder
    {
        return $query->whereBlind('description', 'description_index', $description);
    }
}
