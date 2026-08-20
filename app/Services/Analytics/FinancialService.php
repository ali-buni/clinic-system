<?php

namespace App\Services\Analytics;

use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialService
{
    private const VALID_PERIODS = ['year', 'month', 'day', 'total'];

    private function normalizePeriod(string $period): string
    {
        return in_array($period, self::VALID_PERIODS, true) ? $period : 'month';
    }

    private function getPeriodKey(Carbon $date, string $period): string
    {
        return match ($period) {
            'year' => $date->format('Y'),
            'month' => $date->format('Y-m'),
            'day' => $date->format('Y-m-d'),
            default => 'total',
        };
    }

    private function applyDateRange($query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to.' 23:59:59');
        }
    }

    public function getRevenueByPeriod(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $invoices = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->with([]);
        $this->applyDateRange($invoices, $from, $to);
        $invoices = $invoices->get();

        if ($period === 'total') {
            return collect([(object) [
                'total_revenue' => $invoices->sum('total_cost'),
            ]]);
        }

        $grouped = $invoices->groupBy(fn ($inv) => $this->getPeriodKey($inv->created_at, $period));

        return $grouped->map(fn ($group, $periodKey) => (object) [
            'period' => $periodKey,
            'total_revenue' => $group->sum('total_cost'),
        ])->values()->sortBy('period')->values();
    }

    public function getRevenueByDoctor(int $clinicId, string $period = 'total', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $invoices = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->with(['appointment.doctor.user']);
        $this->applyDateRange($invoices, $from, $to);
        $invoices = $invoices->get();

        $grouped = $invoices->groupBy(fn ($inv) => $inv->appointment?->doctor_id ?? 'unknown');

        $result = $grouped->map(function ($group, $doctorId) use ($period) {
            $doctor = $group->first()?->appointment?->doctor?->user;
            $doctorName = $doctor ? trim($doctor->fname.' '.$doctor->lname) : 'Unknown';

            $data = (object) [
                'doctor_id' => $doctorId,
                'doctor_name' => $doctorName,
                'total_revenue' => $group->sum('total_cost'),
            ];

            if ($period !== 'total') {
                $periodGrouped = $group->groupBy(fn ($inv) => $this->getPeriodKey($inv->created_at, $period));
                $data->periods = $periodGrouped->map(fn ($periodGroup, $periodKey) => (object) [
                    'period' => $periodKey,
                    'total_revenue' => $periodGroup->sum('total_cost'),
                ])->values();
            }

            return $data;
        });

        return $result->values()->sortByDesc('total_revenue')->values();
    }

    public function getOutstandingBalance(int $clinicId, string $period = 'total', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $invoices = Invoice::where('clinic_id', $clinicId)
            ->whereNotIn('status', ['paid'])
            ->with([]);
        $this->applyDateRange($invoices, $from, $to);
        $invoices = $invoices->get();

        if ($period === 'total') {
            return collect([(object) [
                'outstanding_balance' => $invoices->sum('total_cost'),
            ]]);
        }

        $grouped = $invoices->groupBy(fn ($inv) => $this->getPeriodKey($inv->created_at, $period));

        return $grouped->map(fn ($group, $periodKey) => (object) [
            'period' => $periodKey,
            'outstanding_balance' => $group->sum('total_cost'),
        ])->values()->sortBy('period')->values();
    }
}
