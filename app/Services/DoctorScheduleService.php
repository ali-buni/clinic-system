<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Work_hour;
use Exception;
use Carbon\Carbon;

class DoctorScheduleService
{
    /**
     * 1. إضافة ساعة دوام جديدة مع فحص تضارب الغرفة
     */
    public function createWorkHour(Doctor $doctor, array $data): Work_hour
    {
        // 🚨 رمي خطأ في حال تكرار اليوم
        if ($this->dayAlreadyExists($doctor, $data['day_of_week'])) {
            throw new Exception('day_already_exists');
        }

        // 🚨 رمي خطأ في حال تضارب الغرفة
        if (!$doctor->room_id || $this->hasRoomConflict($doctor->room_id, $data['day_of_week'], $data['start_time'], $data['end_time'])) {
            throw new Exception('room_conflict');
        }

        // هنا نضمن مية بالمية أن الدالة لا ترجع إلا الموديل فقط!
        return $doctor->workHours()->create($data);
    }
    
    public function updateWorkHour(Doctor $doctor, $workHourId, array $data): Work_hour
    {
        $workHour = $doctor->workHours()->find($workHourId);
        if (!$workHour) {
            throw new Exception('record_not_found');
        }
        if (isset($data['day_of_week']) && $data['day_of_week'] != $workHour->day_of_week) {
            if ($this->dayAlreadyExists($doctor, $data['day_of_week'])) {
                throw new Exception('day_already_exists');
            }
        }
        // أ. فحص تضارب مواعيد المرضى
        if ($this->hasAppointmentConflict($doctor, $workHour, $data)) {
            throw new Exception('appointment_conflict');
            }
            
        // ب. فحص تضارب الغرفة مع طبيب آخر
        $day = $data['day_of_week'] ?? $workHour->day_of_week;
        $start = $data['start_time'] ?? $workHour->start_time;
        $end = $data['end_time'] ?? $workHour->end_time;

        if (!$doctor->room_id || $this->hasRoomConflict($doctor->room_id, $day, $start, $end, $workHourId)) {
            throw new Exception('room_conflict');
        }
        
        $workHour->update($data);
        return $workHour;
    }

    public function deleteWorkHour(Doctor $doctor, $workHourId): bool
    {
        $workHour = $doctor->workHours()->find($workHourId);
        if (!$workHour) {
            throw new Exception('record_not_found');
        }

        // إذا في مواعيد مستقبلية مربوطة بهذا الوقت، بنرمي خطأ فوراً ونمنع الحذف
        if ($this->hasAppointmentConflict($doctor, $workHour, ['delete' => true])) {
            throw new Exception('appointment_conflict');
        }

        return $workHour->delete();
    }
    
    public function getDoctorWeeklySchedule(Doctor $doctor): \Illuminate\Support\Collection
    {
        return $doctor->workHours()
            ->where('is_active', true)
            ->orderBy('day_of_week', 'asc')
            ->get(['id', 'day_of_week', 'start_time', 'end_time', 'break_start', 'break_end']);
    }

    public function getDoctorWorkingDays(Doctor $doctor): array
    {
        return $doctor->workHours()
            ->where('is_active', true)
            ->pluck('day_of_week') // بياخد بس عمود الـ day_of_week كـ مصفوفة سادة
            ->toArray();
    }


    public function getDoctorWorkHourByDate(Doctor $doctor, string $date): ?Work_hour
    {
        $targetDate = Carbon::parse($date);
        
        $dayOfWeek = $targetDate->dayOfWeek;

        return $doctor->workHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first(['id', 'day_of_week', 'start_time', 'end_time', 'break_start', 'break_end', 'max_patients_per_day']);
    }
    //Supportive Functions>>>>>>>>>>>>>>
    private function hasRoomConflict($roomId, $dayOfWeek, $startTime, $endTime, $ignoreWorkHourId = null): bool
    {
        $query = Work_hour::where('day_of_week', $dayOfWeek)
            ->whereHas('doctor', function ($q) use ($roomId) {
                $q->where('room_id', $roomId); // الدكاترة اللي بنفس الغرفة حصراً
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

            // بحالة التعديل، بنتجاهل السطر اللي عم نعدله حالياً عشان ما يتضارب مع نفسه
        if ($ignoreWorkHourId) {
            $query->where('id', '!=', $ignoreWorkHourId);
        }

        return $query->exists();
    }

    private function hasAppointmentConflict(Doctor $doctor, Work_hour $currentWorkHour, array $newData): bool
    {
        // جلب المواعيد المستقبلية والفعالة فقط
        $appointmentsQuery = $doctor->appointments()
            ->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled'); 

        $isDelete = isset($newData['delete']);
        $dayChanged = isset($newData['day_of_week']) && $newData['day_of_week'] != $currentWorkHour->day_of_week;
        
        // حالة الحذف أو تغيير يوم الأسبوع -> أي موعد مستقبلي بهذا اليوم هو تعارض
        if ($isDelete || $dayChanged) {
            return $appointmentsQuery->get()->filter(function ($appointment) use ($currentWorkHour) {
                return $appointment->start_time->dayOfWeek === $currentWorkHour->day_of_week;
            })->isNotEmpty();
        }

        // حالة تعديل الساعات بنفس اليوم -> فحص إذا المواعيد بتطلع برا النطاق الجديد
        $newStart = $newData['start_time'] ?? $currentWorkHour->start_time;
        $newEnd = $newData['end_time'] ?? $currentWorkHour->end_time;
        
        return $appointmentsQuery->get()->filter(function ($appointment) use ($currentWorkHour, $newStart, $newEnd) {
            if ($appointment->start_time->dayOfWeek !== $currentWorkHour->day_of_week) {
                return false;
            }

            $apptStart = $appointment->start_time->format('H:i');
            $apptEnd = $appointment->end_time->format('H:i');

            return ($apptStart < $newStart) || ($apptEnd > $newEnd);
            })->isNotEmpty();
    }
    private function dayAlreadyExists(Doctor $doctor, int $dayOfWeek): bool
    {
        return $doctor->workHours()->where('day_of_week', $dayOfWeek)->exists();
    }

}