<?php

namespace App\Services\Analytics;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    private const VALID_PERIODS = ['year', 'month', 'day', 'total'];

    private function normalizePeriod(string $period): string
    {
        return in_array($period, self::VALID_PERIODS, true) ? $period : 'month';
    }

    private function periodColumns(string $period): ?array
    {
        return match ($period) {
            'year' => [
                DB::raw("YEAR(invoices.created_at) as period"),
                DB::raw("YEAR(invoices.created_at)"),
            ],
            'day' => [
                DB::raw("DATE(invoices.created_at) as period"),
                DB::raw("DATE(invoices.created_at)"),
            ],
            'month' => [
                DB::raw("DATE_FORMAT(invoices.created_at, '%Y-%m') as period"),
                DB::raw("DATE_FORMAT(invoices.created_at, '%Y-%m')"),
            ],
            default => null,
        };
    }

    private function applyDateRange($query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->where('invoices.created_at', '>=', $from);
        }
        if ($to) {
            $query->where('invoices.created_at', '<=', $to . ' 23:59:59');
        }
    }

    public function getRevenueByPeriod(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('invoices')
            ->where('invoices.clinic_id', $clinicId)
            ->where('invoices.status', 'paid')
            ->whereNull('invoices.deleted_at');

        $this->applyDateRange($query, $from, $to);

        if ($period === 'total') {
            return $query->select(
                DB::raw('COALESCE(SUM(invoices.total_cost), 0) as total_revenue')
            )->get();
        }

        [$selectCol, $groupCol] = $this->periodColumns($period);

        return $query
            ->select($selectCol, DB::raw('COALESCE(SUM(invoices.total_cost), 0) as total_revenue'))
            ->groupBy($groupCol)
            ->orderBy('period')
            ->get();
    }

    public function getRevenueByDoctor(int $clinicId, string $period = 'total', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('invoices')
            ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->where('appointments.clinic_id', $clinicId)
            ->where('invoices.status', 'paid')
            ->whereNull('invoices.deleted_at')
            ->select(
                'doctors.id as doctor_id',
                DB::raw("CONCAT(users.fname, ' ', users.lname) as doctor_name"),
                DB::raw('COALESCE(SUM(invoices.total_cost), 0) as total_revenue')
            )
            ->groupBy('doctors.id', 'users.fname', 'users.lname');

        $this->applyDateRange($query, $from, $to);

        if ($period !== 'total') {
            [$selectCol, $groupCol] = $this->periodColumns($period);
            $query->addSelect($selectCol)
                ->groupBy($groupCol);
        }

        return $query->orderBy('total_revenue', 'desc')->get();
    }

    public function getOutstandingBalance(int $clinicId, string $period = 'total', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('invoices')
            ->where('invoices.clinic_id', $clinicId)
            ->whereNotIn('invoices.status', ['paid'])
            ->whereNull('invoices.deleted_at');

        $this->applyDateRange($query, $from, $to);

        if ($period === 'total') {
            return $query->select(
                DB::raw('COALESCE(SUM(invoices.total_cost), 0) as outstanding_balance')
            )->get();
        }

        [$selectCol, $groupCol] = $this->periodColumns($period);

        return $query
            ->select($selectCol, DB::raw('COALESCE(SUM(invoices.total_cost), 0) as outstanding_balance'))
            ->groupBy($groupCol)
            ->orderBy('period')
            ->get();
    }
}
