<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

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
        'refunded_amount' => 'decimal:2',
    ];

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
