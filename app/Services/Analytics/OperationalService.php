<?php

namespace App\Services\Analytics;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Work_hour;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Support\DatabaseHelper;
use Illuminate\Support\Facades\DB;

class OperationalService
{
    private const VALID_PERIODS = ['year', 'month', 'day', 'total'];

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function getAppointmentsByPeriod(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('appointments')
            ->where('appointments.clinic_id', $clinicId)
            ->whereNull('appointments.deleted_at');

        $this->applyDateRange($query, $from, $to);

        $select = "
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        ";

        if ($period === 'total') {
            return $query->selectRaw($select)->get();
        }

        [$selectCol] = $this->periodColumns($period);

        return $query
            ->selectRaw("{$selectCol}, {$select}")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function getCompletionByPeriod(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('appointments')
            ->where('appointments.clinic_id', $clinicId)
            ->whereNull('appointments.deleted_at');

        $this->applyDateRange($query, $from, $to);

        $select = "
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            ROUND(COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 0), 2) as completion_rate
        ";

        if ($period === 'total') {
            return $query->selectRaw($select)->get();
        }

        [$selectCol] = $this->periodColumns($period);

        return $query
            ->selectRaw("{$selectCol}, {$select}")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function getNoShowByPeriod(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);

        $query = DB::table('appointments')
            ->where('appointments.clinic_id', $clinicId)
            ->whereNull('appointments.deleted_at');

        $this->applyDateRange($query, $from, $to);

        $select = "
            COUNT(*) as total,
            SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
            ROUND(COALESCE(SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 0), 2) as no_show_rate
        ";

        if ($period === 'total') {
            return $query->selectRaw($select)->get();
        }

        [$selectCol] = $this->periodColumns($period);

        return $query
            ->selectRaw("{$selectCol}, {$select}")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function getDoctorUtilization(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): Collection
    {
        $period = $this->normalizePeriod($period);
        $now = Carbon::now();
        $fromDate = $from ? Carbon::parse($from) : $now->copy()->subMonth();
        $toDate = $to ? Carbon::parse($to) : $now;
        $defaultMinutes = (int) $this->settings->get('default_work_minutes', 480);

        $doctors = Doctor::with('user')
            ->where('clinic_id', $clinicId)
            ->get()
            ->keyBy('id');

        if ($doctors->isEmpty()) {
            return collect();
        }

        $periodRanges = $this->generatePeriodRanges($period, $fromDate, $toDate);

        $periodExpr = match ($period) {
            'year'  => DatabaseHelper::dateFormat('start_time', '%Y'),
            'month' => DatabaseHelper::dateFormat('start_time', '%Y-%m'),
            'day'   => "DATE(start_time)",
            'total' => "'total'",
        };

        $apptData = DB::table('appointments')
            ->whereIn('doctor_id', $doctors->pluck('id'))
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->whereNull('deleted_at')
            ->selectRaw("{$periodExpr} as period_key, doctor_id, start_time, end_time")
            ->get()
            ->filter(fn($a) => $a->start_time && $a->end_time);

        $bookedByDoctorPeriod = collect();
        foreach ($apptData->groupBy('doctor_id') as $docId => $rows) {
            $bookedByDoctorPeriod[$docId] = $rows->groupBy('period_key')->map(
                fn($items) => $items->sum(fn($a) => Carbon::parse($a->start_time)->diffInMinutes(Carbon::parse($a->end_time)))
            );
        }

        $result = [];
        foreach ($doctors as $doctor) {
            $doctorPeriods = $bookedByDoctorPeriod->get($doctor->id, collect());
            $periods = [];

            foreach ($periodRanges as $pr) {
                $key = $pr['key'];
                $workDays = $pr['work_days'];
                $bookedMinutes = (int) ($doctorPeriods[$key] ?? 0);
                $availableMinutes = $workDays * $defaultMinutes;
                $utilization = $availableMinutes > 0
                    ? round(($bookedMinutes / $availableMinutes) * 100, 2)
                    : 0;

                $periods[] = [
                    'period'       => $key,
                    'from'         => $pr['from']->toDateString(),
                    'to'           => $pr['to']->toDateString(),
                    'booked_minutes' => $bookedMinutes,
                    'available_minutes' => $availableMinutes,
                    'utilization_rate' => $utilization . '%',
                ];
            }

            $result[] = [
                'doctor_id'   => $doctor->id,
                'doctor_name' => $doctor->user?->fname . ' ' . $doctor->user?->lname,
                'periods'     => $periods,
            ];
        }

        return collect($result);
    }

    private function generatePeriodRanges(string $period, Carbon $from, Carbon $to): array
    {
        if ($period === 'total') {
            return [[
                'key'       => 'total',
                'from'      => $from,
                'to'        => $to,
                'work_days' => $this->countWorkDays($from, $to),
            ]];
        }

        $ranges = [];
        $cursor = match ($period) {
            'year'  => $from->copy()->startOfYear(),
            'month' => $from->copy()->startOfMonth(),
            'day'   => $from->copy()->startOfDay(),
        };

        while ($cursor->lte($to)) {
            $end = match ($period) {
                'year'  => $cursor->copy()->endOfYear(),
                'month' => $cursor->copy()->endOfMonth(),
                'day'   => $cursor->copy()->endOfDay(),
            };

            $rangeStart = $cursor->copy()->max($from);
            $rangeEnd = $end->copy()->min($to);

            $key = match ($period) {
                'year'  => $cursor->format('Y'),
                'month' => $cursor->format('Y-m'),
                'day'   => $cursor->format('Y-m-d'),
            };

            $ranges[] = [
                'key'       => $key,
                'from'      => $rangeStart,
                'to'        => $rangeEnd,
                'work_days' => $this->countWorkDays($rangeStart, $rangeEnd),
            ];

            match ($period) {
                'year'  => $cursor->addYear(),
                'month' => $cursor->addMonth(),
                'day'   => $cursor->addDay(),
            };
        }

        return $ranges;
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, self::VALID_PERIODS, true) ? $period : 'month';
    }

    private function periodColumns(string $period): ?array
    {
        return match ($period) {
            'year' => [
                DatabaseHelper::dateFormat('start_time', '%Y') . " as period",
                DatabaseHelper::dateFormat('start_time', '%Y'),
            ],
            'day' => [
                "DATE(start_time) as period",
                "DATE(start_time)",
            ],
            'month' => [
                DatabaseHelper::dateFormat('start_time', '%Y-%m') . " as period",
                DatabaseHelper::dateFormat('start_time', '%Y-%m'),
            ],
            default => null,
        };
    }

    private function applyDateRange($query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->where('appointments.start_time', '>=', $from);
        }
        if ($to) {
            $query->where('appointments.start_time', '<=', $to . ' 23:59:59');
        }
    }

    private function countWorkDays(Carbon $from, Carbon $to): int
    {
        return $from->diffInDaysFiltered(
            fn(Carbon $d) => $d != null,
            $to,
        );
    }
}
