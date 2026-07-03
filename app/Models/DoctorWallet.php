<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorWallet extends Model
{
    protected $fillable = [
        'doctor_id',
        'balance',
        'pending_withdrawal',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_withdrawal' => 'decimal:2',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getAvailableBalance(): float
    {
        return (float) $this->balance;
    }

    public function addToBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    public function deductFromBalance(float $amount): void
    {
        $this->decrement('balance', $amount);
    }

    public function addPending(float $amount): void
    {
        $this->increment('pending_withdrawal', $amount);
    }

    public function removePending(float $amount): void
    {
        $this->decrement('pending_withdrawal', $amount);
    }
}
