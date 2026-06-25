<?php

namespace App\Services\Analytics;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    // داخل FinancialService.php
    public function getRevenueOverview(int $clinicId)
    {
        return DB::table('invoices')
            ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->where('appointments.clinic_id', $clinicId)
            ->where('invoices.status', 'paid')
            ->whereNull('invoices.deleted_at')
            ->select(
                DB::raw("CONCAT(users.fname, ' ', users.lname) as doctor_name"),
                DB::raw("SUM(invoices.total_cost) as total_revenue")
            )
            ->groupBy('doctors.id', 'users.fname', 'users.lname')
            ->get();
    }

    public function getOutstandingBalance(int $clinicId)
    {
        return Invoice::where('clinic_id', $clinicId)
            ->where('status', 'unpaid')
            ->sum('total_cost');
    }
}
