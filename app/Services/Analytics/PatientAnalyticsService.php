<?php

namespace App\Services\Analytics;

use App\Models\PatientInfo;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientAnalyticsService
{
    public function getRetentionMetrics(int $clinicId, string $period = 'total'): array
    {
        $period = in_array($period, ['year', 'month', 'day', 'total']) ? $period : 'total';

        if ($period === 'total') {
            $totalPatients = PatientInfo::where('clinic_id', $clinicId)->count();

            $returningPatients = Appointment::where('clinic_id', $clinicId)
                ->where('status', 'completed')
                ->selectRaw('patient_id, count(*) as cnt')
                ->groupBy('patient_id')
                ->having('cnt', '>', 1)
                ->get()
                ->count();

            $retentionRate = $totalPatients > 0
                ? ($returningPatients / $totalPatients) * 100
                : 0;

            return [
                'total_patients'     => $totalPatients,
                'returning_patients' => $returningPatients,
                'retention_rate'     => round($retentionRate, 2) . '%',
            ];
        }

        $appts = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->get(['patient_id', 'created_at']);

        $periodKey = match ($period) {
            'year' => fn($d) => $d->format('Y'),
            'day'  => fn($d) => $d->format('Y-m-d'),
            default => fn($d) => $d->format('Y-m'),
        };

        $grouped = $appts->groupBy(fn($a) => $periodKey($a->created_at));

        $result = [];
        foreach ($grouped as $key => $items) {
            $patientCounts = $items->groupBy('patient_id')->map->count();
            $total = $patientCounts->count();
            $returning = $patientCounts->filter(fn($c) => $c > 1)->count();

            $result[] = [
                'period'            => $key,
                'total_patients'    => $total,
                'returning_patients' => $returning,
                'retention_rate'    => $total > 0
                    ? round(($returning / $total) * 100, 2) . '%'
                    : '0%',
            ];
        }

        return $result;
    }

    public function getLostPatients(int $clinicId, int $months = 6, string $groupBy = 'total'): array
    {
        $groupBy = in_array($groupBy, ['year', 'month', 'day', 'total']) ? $groupBy : 'total';

        $firstLast = Appointment::where('clinic_id', $clinicId)
            ->selectRaw('patient_id, MIN(created_at) as first_visit, MAX(created_at) as last_visit')
            ->groupBy('patient_id')
            ->get();

        if ($firstLast->isEmpty()) {
            return $groupBy === 'total'
                ? ['count_lost' => 0, 'count_new' => 0]
                : [];
        }

        $cutoff = Carbon::now()->subMonths($months);

        if ($groupBy === 'total') {
            $countLost = $firstLast->filter(fn($p) => $p->last_visit < $cutoff)->count();
            $countNew  = $firstLast->filter(fn($p) => $p->first_visit >= $cutoff)->count();

            return [
                'count_lost' => $countLost,
                'count_new'  => $countNew,
            ];
        }

        $periodKey = match ($groupBy) {
            'year' => fn($d) => Carbon::parse($d)->format('Y'),
            'day'  => fn($d) => Carbon::parse($d)->format('Y-m-d'),
            default => fn($d) => Carbon::parse($d)->format('Y-m'),
        };

        $lostByPeriod = $firstLast
            ->filter(fn($p) => $p->last_visit < $cutoff)
            ->groupBy(fn($p) => $periodKey($p->last_visit))
            ->map(fn($items) => $items->count());

        $newByPeriod = $firstLast
            ->filter(fn($p) => $p->first_visit >= $cutoff)
            ->groupBy(fn($p) => $periodKey($p->first_visit))
            ->map(fn($items) => $items->count());

        $allPeriods = $lostByPeriod->keys()->merge($newByPeriod->keys())->unique()->sort();

        $result = [];
        foreach ($allPeriods as $key) {
            $result[] = [
                'period'     => $key,
                'count_lost' => $lostByPeriod->get($key, 0),
                'count_new'  => $newByPeriod->get($key, 0),
            ];
        }

        return $result;
    }
}
