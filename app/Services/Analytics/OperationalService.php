<?php

namespace App\Services\Analytics;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Work_hour;
use Carbon\Carbon;
use App\Services\Analytics\SettingService;

class OperationalService
{
    public function getDoctorUtilization(int $clinicId, string $date)
    {
        $doctors = Doctor::where('clinic_id', $clinicId)->get();
        $report = [];

        foreach ($doctors as $doctor) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $workHour = Work_hour::where('doctor_id', $doctor->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            $defaultMinutes = SettingService::get('default_work_minutes', 480);
            if (!$workHour) {
                $totalAvailableMinutes = $defaultMinutes;
            }
            else {

            // حساب الساعات المتاحة (بالدقائق)
            $start = Carbon::parse($workHour->start_time);
            $end = Carbon::parse($workHour->end_time);
            $totalAvailableMinutes = $start->diffInMinutes($end);

            // 2. جلب المواعيد المؤكدة لهذا الطبيب في هذا اليوم
            $appointments = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('start_time', $date)
                ->where('status', 'completed')
                ->get();

            // حساب مجموع وقت المواعيد الفعلية
            $totalBookedMinutes = $appointments->sum(function ($app) {
                return Carbon::parse($app->start_time)->diffInMinutes(Carbon::parse($app->end_time));
            });

            // 3. حساب النسبة
            $utilization = ($totalAvailableMinutes > 0)
                ? round(($totalBookedMinutes / $totalAvailableMinutes) * 100, 2)
                : 0;

            $report[] = [
                'doctor_name' => $doctor->user->fname . ' ' . $doctor->user->lname,
                'appointments_count' => $appointments->count(),
                'available_hours' => round($totalAvailableMinutes / 60, 1),
                'utilization_rate' => $utilization . '%'
            ];
            }
        }

        return $report;
    }
}
