<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Schedule_override;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class ScheduleOverrideService
{
    public function create(Doctor $doctor, array $data): Schedule_override
    {
        $this->validateNoOverlap($doctor, $data);

        return $doctor->scheduleOverrides()->create($data);
    }

    public function update(Doctor $doctor, int $overrideId, array $data): Schedule_override
    {
        $override = $doctor->scheduleOverrides()->find($overrideId);

        if (!$override) {
            throw new Exception('record_not_found');
        }

        $merged = array_merge($override->toArray(), $data);
        $this->validateNoOverlap($doctor, $merged, $overrideId);

        $override->update($data);
        return $override->fresh();
    }

    public function delete(Doctor $doctor, int $overrideId): bool
    {
        $override = $doctor->scheduleOverrides()->find($overrideId);

        if (!$override) {
            throw new Exception('record_not_found');
        }

        return $override->delete();
    }

    public function get(Doctor $doctor, int $overrideId): ?Schedule_override
    {
        return $doctor->scheduleOverrides()->find($overrideId);
    }

    public function getByDoctor(Doctor $doctor): Collection
    {
        return $doctor->scheduleOverrides()->orderBy('override_date')->get();
    }

    public function getByDate(Doctor $doctor, string $date): ?Schedule_override
    {
        return $doctor->scheduleOverrides()->whereDate('override_date', $date)->first();
    }

    public function getByDateRange(Doctor $doctor, string $from, string $to): Collection
    {
        return $doctor->scheduleOverrides()
            ->whereBetween('override_date', [$from, $to])
            ->orderBy('override_date')
            ->get();
    }

    private function validateNoOverlap(Doctor $doctor, array $data, int $ignoreId = 0): void
    {
        $date = $data['override_date'] ?? null;
        if (!$date) {
            return;
        }

        $query = Schedule_override::where('doctor_id', $doctor->id)
            ->whereDate('override_date', $date);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->first();

        if ($existing) {
            $isNewClosed = isset($data['is_closed']) && $data['is_closed'];
            $existingClosed = $existing->is_closed;

            if ($isNewClosed || $existingClosed) {
                throw new Exception('override_date_conflict');
            }

            $newStart = isset($data['start_time']) ? Carbon::parse($data['start_time']) : null;
            $newEnd   = isset($data['end_time']) ? Carbon::parse($data['end_time']) : null;
            $existingStart = $existing->start_time ? Carbon::parse($existing->start_time) : null;
            $existingEnd   = $existing->end_time ? Carbon::parse($existing->end_time) : null;

            if ($newStart && $newEnd && $existingStart && $existingEnd) {
                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    throw new Exception('override_time_conflict');
                }
            }
        }
    }
}
