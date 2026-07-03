<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class DoctorEarningsService
{
    private const DOCTOR_SHARE_PERCENTAGE = 0.98;

    public function calculateGrossEarnings(Doctor $doctor): float
    {
        return (float) DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->where('appointments.doctor_id', $doctor->id)
            ->whereNotNull('payments.paid_at')
            ->whereNull('payments.deleted_at')
            ->sum('payments.amount');
    }

    public function calculateTotalRefunded(Doctor $doctor): float
    {
        return (float) DB::table('refunds')
            ->join('payments', 'refunds.payment_id', '=', 'payments.id')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->where('appointments.doctor_id', $doctor->id)
            ->sum('refunds.amount');
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
