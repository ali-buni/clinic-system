<?php

namespace App\Services\Analytics;

use App\Models\Appointment;
use Carbon\Carbon;
use App\Services\Analytics\SettingService;

class PredictiveService
{
    public function calculateNoShowProbability(int $patientId)
    {
        $totalAppointments = Appointment::where('patient_id', $patientId)->count();
        $noShowAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'no_show')
            ->count();

        if ($totalAppointments == 0) return 0;

        return round(($noShowAppointments / $totalAppointments) * 100, 2);
    }

    public function getCrowdingRisk(int $clinicId)
    {
        $threshold = SettingService::get('crowding_threshold', 20);

        $avg = Appointment::where('clinic_id', $clinicId)
            ->where('start_time', '>=', \Carbon\Carbon::now()->subWeeks(4))
            ->count() / 4;

        return ($avg > $threshold) ? 'High' : 'Normal';
    }
}
