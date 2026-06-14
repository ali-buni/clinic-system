<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Work_hour;
use Exception;
use Carbon\Carbon;

class DoctorScheduleService
{
    public function createWorkHour(Doctor $doctor, array $data): Work_hour
    {
        if ($this->dayAlreadyExists($doctor, $data['day_of_week'])) {
            throw new Exception('day_already_exists');
        }

        if (!$doctor->room_id || $this->hasRoomConflict($doctor->room_id, $data['day_of_week'], $data['start_time'], $data['end_time'])) {
            throw new Exception('room_conflict');
        }

        return $doctor->workHours()->create($data);
    }

    public function updateWorkHour(Doctor $doctor, int $dayOfWeek, array $data): Work_hour
    {
        $workHour = $doctor->workHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('doctor_id', $doctor->id)
            ->first();
        if (!$workHour) {
            throw new Exception('record_not_found');
        }
        if (isset($data['day_of_week']) && $data['day_of_week'] != $workHour->day_of_week) {
            if ($this->dayAlreadyExists($doctor, $data['day_of_week'])) {
                throw new Exception('day_already_exists');
            }
        }
        if ($this->hasAppointmentConflict($doctor, $workHour, $data)) {
            throw new Exception('appointment_conflict');
        }

        $day = $data['day_of_week'] ?? $workHour->day_of_week;
        $start = $data['start_time'] ?? $workHour->start_time;
        $end = $data['end_time'] ?? $workHour->end_time;

        if (!$doctor->room_id || $this->hasRoomConflict($doctor->room_id, $day, $start, $end, $workHour->id)) {
            throw new Exception('room_conflict');
        }

        $workHour->update($data);
        return $workHour;
    }

    public function deleteWorkHour(Doctor $doctor, int $workHourId): bool
    {
        $workHour = $doctor->workHours()->find($workHourId);
        if (!$workHour) {
            throw new Exception('record_not_found');
        }

        if ($this->hasAppointmentConflict($doctor, $workHour, ['delete' => true])) {
            throw new Exception('appointment_conflict');
        }

        return $workHour->forceDelete();
    }

    public function getDoctorWeeklySchedule(Doctor $doctor): \Illuminate\Support\Collection
    {
        return $doctor->workHours()
            ->get();
    }

    public function getDoctorWorkHourByDate(Doctor $doctor, string $date): ?Work_hour
    {
        $targetDate = Carbon::parse($date);

        $dayOfWeek = $targetDate->dayOfWeek;

        return $doctor->workHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();
    }

    private function hasRoomConflict(int $roomId, int $dayOfWeek, string $startTime, string $endTime, int $ignoreWorkHourId = 0): bool
    {
        $query = Work_hour::where('day_of_week', $dayOfWeek)
            ->whereHas('doctor', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });

        if ($ignoreWorkHourId) {
            $query->where('id', '!=', $ignoreWorkHourId);
        }

        return $query->exists();
    }

    private function hasAppointmentConflict(Doctor $doctor, Work_hour $currentWorkHour, array $newData): bool
    {
        $isDelete = isset($newData['delete']);
        $dayChanged = isset($newData['day_of_week']) && $newData['day_of_week'] != $currentWorkHour->day_of_week;

        // If deleting or changing day, check appointments on the current day
        if ($isDelete || $dayChanged) {
            return $doctor->appointments()
                ->whereDate('start_time', '>', now())
                ->where('status', 'cancelled')
                ->whereRaw('DAYOFWEEK(start_time) - 1 = ?', [$currentWorkHour->day_of_week])
                ->exists();
        }

        // If changing time range
        $newStart = $newData['start_time'] ?? $currentWorkHour->start_time;
        $newEnd = $newData['end_time'] ?? $currentWorkHour->end_time;

        return $doctor->appointments()
            ->whereDate('start_time', '>', now())
            ->where('status', 'cancelled')
            ->whereRaw('DAYOFWEEK(start_time) - 1 = ?', [$currentWorkHour->day_of_week])
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->whereTime('start_time', '<', $newStart)
                    ->orWhereTime('end_time', '>', $newEnd);
            })
            ->exists();
    }

    private function dayAlreadyExists(Doctor $doctor, int $dayOfWeek): bool
    {
        return $doctor->workHours()->where('day_of_week', $dayOfWeek)->exists();
    }
}
