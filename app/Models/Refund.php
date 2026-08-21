<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Refund extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'amount',
        'reason',
        'refunded_by',
        'stripe_refund_id',
        'idempotency_key',
    ];

    protected $casts = [];

    public static function getEncryptedColumns(): array
    {
        return ['amount', 'stripe_refund_id'];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('amount')
            ->addOptionalTextField('stripe_refund_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
