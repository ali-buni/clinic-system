<?php

namespace App\Services\Analytics;

use App\Models\PatientInfo;
use App\Models\Appointment;
use Carbon\Carbon;

class PatientAnalyticsService
{
    public function getRetentionMetrics(int $clinicId)
    {
        $totalPatients = PatientInfo::where('clinic_id', $clinicId)->count();

        $returningPatients = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->select('patient_id')
            ->groupBy('patient_id')
            ->havingRaw('count(*) > 1')
            ->get()
            ->count();

        $retentionRate = ($totalPatients > 0) ? ($returningPatients / $totalPatients) * 100 : 0;

        return [
            'total_patients' => $totalPatients,
            'returning_patients' => $returningPatients,
            'retention_rate' => round($retentionRate, 2) . '%'
        ];
    }
    public function getLostPatients(int $clinicId)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        return PatientInfo::where('clinic_id', $clinicId)
            ->whereDoesntHave('appointments', function ($query) use ($sixMonthsAgo) {
                $query->where('start_time', '>=', $sixMonthsAgo);
            })
            ->get();
    }
}
