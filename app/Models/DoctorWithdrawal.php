<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorWithdrawal extends Model
{
    protected $fillable = [
        'doctor_id',
        'amount',
        'stripe_transfer_id',
        'stripe_connected_account_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'status' => WithdrawalStatus::class,
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === WithdrawalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === WithdrawalStatus::Approved;
    }

    public function isCompleted(): bool
    {
        return $this->status === WithdrawalStatus::Completed;
    }

    public function isRejected(): bool
    {
        return $this->status === WithdrawalStatus::Rejected;
    }

    public function isFailed(): bool
    {
        return $this->status === WithdrawalStatus::Failed;
    }
}
