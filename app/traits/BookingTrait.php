<?php

namespace App\Traits;

use App\Jobs\SendAppointmentStatusNotificationJob;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Work_hour;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait BookingTrait
{
    /**
     * Get the allowed working intervals for a specific doctor on a given date.
     *
     * @param  string  $date  (Y-m-d)
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public function getAllowedIntervalsForDoctorDate(int $doctorId, string $date): array
    {
        $v = Cache::get("cache_v:doctor:{$doctorId}:interval", 0);

        return Cache::remember("intervals:doctor:{$doctorId}:{$date}:v{$v}", 300, function () use ($doctorId, $date) {
            $doctor = Doctor::findOrFail($doctorId);

            $workHours = Work_hour::where('doctor_id', $doctorId)
                ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
                ->where('is_active', true)
                ->get();

            if ($workHours->isEmpty()) {
                return [];
            }

            $intervals = $this->buildBaseIntervals($workHours, $date);

            $override = $doctor->scheduleOverrides()->whereDate('override_date', $date)->first();
            if ($override) {
                $intervals = $this->applyScheduleOverride($intervals, $override, $date);
            }

            return $this->mergeOverlappingIntervals($intervals);
        });
    }

    /**
     * Generate available single slots for a doctor on a specific date.
     */
    public function getAvailableSlots(int $doctorId, string $date, ?int $excludeAppointmentId = null): array
    {
        if ($excludeAppointmentId !== null) {
            return $this->computeSlots($doctorId, $date, $excludeAppointmentId);
        }

        $v = Cache::get("cache_v:doctor:{$doctorId}:slot", 0);

        return Cache::remember("slots:doctor:{$doctorId}:{$date}:v{$v}", 60, function () use ($doctorId, $date) {
            return $this->computeSlots($doctorId, $date);
        });
    }

    private function computeSlots(int $doctorId, string $date, ?int $excludeAppointmentId = null): array
    {
        $doctor = Doctor::findOrFail($doctorId);
        $workHours = Work_hour::where('doctor_id', $doctorId)
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($workHours->isEmpty() || $this->isDailyPatientLimitReached($doctorId, $date, $workHours)) {
            return [];
        }

        $slotMinutes = ($doctor->appointment_duration ?? 30);
        $intervals = $this->getAllowedIntervalsForDoctorDate($doctorId, $date);
        if (empty($intervals)) {
            return [];
        }

        $existing = Appointment::scheduledInDate($doctorId, $date)
            ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
                $query->where('id', '!=', $excludeAppointmentId);
            })->get()
            ->map(function ($a) {
                return ['start' => Carbon::parse($a->start_time), 'end' => Carbon::parse($a->end_time)];
            })->toArray();

        $slots = [];
        foreach ($intervals as [$s, $e]) {
            $cursor = $s->copy();
            while (true) {
                $slotStart = $cursor->copy();
                $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);
                if ($slotEnd->gt($e)) {
                    break;
                }

                $overlap = false;
                foreach ($existing as $b) {
                    if ($slotStart->lt($b['end']) && $slotEnd->gt($b['start'])) {
                        $overlap = true;
                        break;
                    }
                }
                if (! $overlap) {
                    $slots[] = ['start' => $slotStart->toDateTimeString(), 'end' => $slotEnd->toDateTimeString()];
                }

                $cursor->addMinutes($slotMinutes);
            }
        }

        return $slots;
    }

    /**
     * Book a new appointment (Supports multi-slot durations).
     *
     * @throws Exception
     */
    public function bookAppointment(int $doctorId, string $date, string $startTime, string $endTime, array $attributes = []): Appointment
    {
        return DB::transaction(function () use ($doctorId, $date, $startTime, $endTime, $attributes) {
            if (Carbon::parse($date)->lte(Carbon::now()->startOfDay())) {
                throw new Exception('Appointment date must be at least 1 day in advance.');
            }

            Work_hour::where('doctor_id', $doctorId)->lockForUpdate()->first();

            $start = Carbon::parse($date.' '.$startTime);
            $end = Carbon::parse($date.' '.$endTime);

            $workHours = Work_hour::where('doctor_id', $doctorId)
                ->where('day_of_week', $start->dayOfWeek)
                ->where('is_active', true)
                ->get();

            if (count($workHours) === 0) {
                throw new Exception('no work hour valid for this date');
            }

            if ($this->isDailyPatientLimitReached($doctorId, $date, $workHours)) {
                throw new Exception('Booking failed: The doctor has reached the maximum patient limit for this day.');
            }

            if (! $this->isTimeRangeAvailable($doctorId, $date, $start, $end)) {
                throw new Exception('Booking failed: The selected time frame is unavailable or overlaps with an existing appointment.');
            }

            $appointment = Appointment::create(
                array_merge([
                    'doctor_id' => $doctorId,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => 'scheduled',
                ], $attributes)
            )->load(['type', 'room', 'patient', 'doctor']);
            Cache::increment("cache_v:doctor:{$doctorId}:slot");

            app(InvoiceService::class)->createBookingInvoice($appointment);

            SendAppointmentStatusNotificationJob::dispatch(
                $appointment->id,
                'booked',
                'doctor_and_secretary',
                $appointment->start_time?->toDateString(),
                $appointment->start_time?->format('H:i')
            );

            return $appointment;
        }, attempts: 3);
    }

    /**
     * Update/Reschedule an existing appointment safely.
     *
     * @throws Exception
     */
    public function updateAppointment(int $appointmentId, string $date, string $startTime, string $endTime, array $attributes = []): Appointment
    {
        return DB::transaction(function () use ($appointmentId, $date, $startTime, $endTime, $attributes) {
            if (Carbon::parse($date)->lte(Carbon::now()->addDay()->startOfDay())) {
                throw new Exception('Appointment date must be at least 1 day in advance.');
            }

            $appointment = Appointment::lockForUpdate()->findOrFail($appointmentId);
            $doctorId = $appointment->doctor_id;

            $start = Carbon::parse($date.' '.$startTime);
            $end = Carbon::parse($date.' '.$endTime);

            $workHours = Work_hour::where('doctor_id', $doctorId)
                ->where('day_of_week', $start->dayOfWeek)
                ->where('is_active', true)
                ->get();

            if ($this->isDailyPatientLimitReached($doctorId, $date, $workHours, $appointmentId)) {
                throw new Exception('Update failed: The doctor has reached the maximum patient limit for this day.');
            }

            if (! $this->isTimeRangeAvailable($doctorId, $date, $start, $end, $appointmentId)) {
                throw new Exception('Update failed: The new time frame is unavailable or overlaps with another appointment.');
            }

            $previousStartTime = $appointment->start_time?->copy();

            $appointment->update(array_merge([
                'start_time' => $start,
                'end_time' => $end,
            ], $attributes));

            Cache::increment("cache_v:doctor:{$doctorId}:slot");

            SendAppointmentStatusNotificationJob::dispatch(
                $appointment->id,
                'updated',
                'doctor_and_secretary',
                $appointment->start_time?->toDateString(),
                $appointment->start_time?->format('H:i'),
                $previousStartTime?->toDateString(),
                $previousStartTime?->format('H:i')
            );

            return $appointment->load(['type', 'room', 'patient', 'doctor']);
        }, attempts: 3);
    }

    /**
     * -------------------------------------------------------------------
     * Private Helper Methods
     * -------------------------------------------------------------------
     */

    /**
     * Check if the doctor's maximum patient capacity for the day has been met.
     */
    private function isDailyPatientLimitReached(int $doctorId, string $date, Collection $workHours, ?int $excludeAppointmentId = null): bool
    {
        $maxPatients = $workHours->first()->max_patients_per_day ?? null;
        if (is_null($maxPatients)) {
            return false;
        }

        $currentBookingsCount = Appointment::allValidInDate($doctorId, $date)
            ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
                $query->where('id', '!=', $excludeAppointmentId);
            })
            ->count();

        return $currentBookingsCount >= $maxPatients;
    }

    /**
     * Check if the requested range breaks down into individual slots that are ALL genuinely available.
     */
    private function isTimeRangeAvailable(int $doctorId, string $date, Carbon $start, Carbon $end, ?int $excludeAppointmentId = null): bool
    {
        $doctor = Doctor::findOrFail($doctorId);
        $slotMinutes = ($doctor->appointment_duration ?? 30);

        $availableSlots = $this->getAvailableSlots($doctorId, $date, $excludeAppointmentId);
        if (empty($availableSlots)) {
            return false;
        }

        $lookup = [];
        foreach ($availableSlots as $slot) {
            $sKey = Carbon::parse($slot['start'])->format('H:i');
            $eKey = Carbon::parse($slot['end'])->format('H:i');
            $lookup["$sKey-$eKey"] = true;
        }

        $cursor = $start->copy();
        $matchedSlotsCount = 0;

        while ($cursor->lt($end)) {
            $slotStartStr = $cursor->format('H:i');
            $slotEndStr = $cursor->addMinutes($slotMinutes)->format('H:i');
            $key = "$slotStartStr-$slotEndStr";

            if (! isset($lookup[$key])) {
                return false;
            }
            $matchedSlotsCount++;
        }

        return $matchedSlotsCount > 0 && $cursor->eq($end);
    }

    /**
     * Build base work intervals excluding break times.
     */
    private function buildBaseIntervals(Collection $workHours, string $date): array
    {
        $intervals = [];
        foreach ($workHours as $wh) {
            $whStart = Carbon::parse($date.' '.$wh->start_time);
            $whEnd = Carbon::parse($date.' '.$wh->end_time);

            $breakStart = $wh->break_start ? Carbon::parse($date.' '.$wh->break_start) : null;
            $breakEnd = $wh->break_end ? Carbon::parse($date.' '.$wh->break_end) : null;

            if ($breakStart && $breakEnd && $breakEnd->gt($breakStart)) {
                if ($whStart->lt($breakStart)) {
                    $intervals[] = [$whStart, $breakStart];
                }
                if ($breakEnd->lt($whEnd)) {
                    $intervals[] = [$breakEnd, $whEnd];
                }
            } else {
                $intervals[] = [$whStart, $whEnd];
            }
        }

        return $intervals;
    }

    /**
     * Apply schedule overrides (closed day or specific time-off adjustments).
     */
    private function applyScheduleOverride(array $intervals, object $override, string $date): array
    {
        if ($override->is_closed) {
            return [];
        }

        if ($override->start_time && $override->end_time) {
            $ovStart = Carbon::parse($date.' '.Carbon::parse($override->start_time)->format('H:i:s'));
            $ovEnd = Carbon::parse($date.' '.Carbon::parse($override->end_time)->format('H:i:s'));

            $newIntervals = [];
            foreach ($intervals as [$s, $e]) {
                if ($e->lte($ovStart)) {
                    $newIntervals[] = [$s, $e];
                } elseif ($s->gte($ovEnd)) {
                    $newIntervals[] = [$s, $e];
                } else {
                    if ($s->lt($ovStart)) {
                        $newIntervals[] = [$s, $ovStart];
                    }
                    if ($e->gt($ovEnd)) {
                        $newIntervals[] = [$ovEnd, $e];
                    }
                }
            }

            return $newIntervals;
        }

        return $intervals;
    }

    /**
     * Sort intervals chronologically and merge overlapping ones.
     */
    private function mergeOverlappingIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        usort($intervals, function ($a, $b) {
            if ($a[0]->eq($b[0])) {
                return 0;
            }

            return $a[0]->lt($b[0]) ? -1 : 1;
        });

        $merged = [];
        foreach ($intervals as $int) {
            if (empty($merged)) {
                $merged[] = $int;

                continue;
            }

            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            if ($int[0]->lte($last[1])) {
                $lastEnd = $int[1]->gt($last[1]) ? $int[1] : $last[1];
                $merged[$lastIndex] = [$last[0], $lastEnd];
            } else {
                $merged[] = $int;
            }
        }

        return $merged;
    }
}
