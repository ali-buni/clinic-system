<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Payment extends Model implements CipherSweetEncrypted
{
    use SoftDeletes, UsesCipherSweet;

    protected $fillable = [
        'payment_method_id',
        'invoice_id',
        'amount',
        'refunded_amount',
        'paid_at',
        'stripe_session_id',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public static function getEncryptedColumns(): array
    {
        return ['amount', 'refunded_amount', 'stripe_session_id', 'stripe_payment_intent_id'];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('amount')
            ->addOptionalTextField('refunded_amount')
            ->addOptionalTextField('stripe_session_id')
            ->addBlindIndex('stripe_session_id', new BlindIndex('stripe_session_id_index'));
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(Payment_method::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getRefundableAmount(): float
    {
        return (float) $this->amount - (float) $this->refunded_amount;
    }
}
