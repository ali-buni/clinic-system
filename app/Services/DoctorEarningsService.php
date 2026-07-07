<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\Payment;
use App\Models\Refund;

class DoctorEarningsService
{
    private const DOCTOR_SHARE_PERCENTAGE = 0.98;

    public function calculateGrossEarnings(Doctor $doctor): float
    {
        $payments = Payment::whereHas('invoice.appointment', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->whereNotNull('paid_at')
            ->get();

        return (float) $payments->sum('amount');
    }

    public function calculateTotalRefunded(Doctor $doctor): float
    {
        $refunds = Refund::whereHas('payment.invoice.appointment', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->get();

        return (float) $refunds->sum('amount');
    }

    public function calculateNetEarnings(Doctor $doctor): float
    {
        $gross = $this->calculateGrossEarnings($doctor);
        $refunded = $this->calculateTotalRefunded($doctor);

        return $gross - $refunded;
    }

    public function calculateDoctorShare(Doctor $doctor): float
    {
        return $this->calculateNetEarnings($doctor) * self::DOCTOR_SHARE_PERCENTAGE;
    }

    public function calculateAvailableBalance(Doctor $doctor): float
    {
        $doctorShare = $this->calculateDoctorShare($doctor);

        $totalWithdrawn = (float) $doctor->withdrawals()
            ->whereIn('status', ['approved', 'completed', 'pending'])
            ->sum('amount');

        return max(0, $doctorShare - $totalWithdrawn);
    }

    public function initializeWallet(Doctor $doctor): DoctorWallet
    {
        return $doctor->wallet()->firstOrCreate([
            'doctor_id' => $doctor->id,
        ], [
            'balance' => 0,
            'pending_withdrawal' => 0,
        ]);
    }

    public function syncWalletBalance(Doctor $doctor): DoctorWallet
    {
        $wallet = $this->initializeWallet($doctor);
        $availableBalance = $this->calculateAvailableBalance($doctor);

        $wallet->update(['balance' => $availableBalance]);

        return $wallet;
    }
}
