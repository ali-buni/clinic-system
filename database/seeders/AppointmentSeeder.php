<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientInfo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $config = require __DIR__ . '/../data/appointment_config.php';
        $visitReasons = require __DIR__ . '/../data/visit_reasons.php';
        $cancelReasons = require __DIR__ . '/../data/cancel_reasons.php';

        $clinic = Clinic::first();
        if (!$clinic) return;

        $doctors = Doctor::all();
        $patients = PatientInfo::all();
        $appointmentTypes = Appointment_type::all();

        $pastStatusPool = array_merge(
            array_fill(0, 5, 'completed'),
            array_fill(0, 2, 'cancelled'),
            array_fill(0, 2, 'no_show')
        );
        $futureStatusPool = array_merge(
            array_fill(0, 3, 'scheduled'),
            ['confirmed']
        );

        $created = 0;
        $usedSlots = [];

        $period = CarbonPeriod::create(
            Carbon::now()->subDays($config['days_past'])->startOfDay(),
            Carbon::now()->addDays($config['days_future'])->startOfDay()
        );
        $workingDays = collect($period)->filter(
            fn($date) =>
            in_array((int)$date->format('w'), $config['working_days'])
        );

        $workHoursCache = $doctors->mapWithKeys(
            fn($d) =>
            [$d->id => $d->workHours->keyBy('day_of_week')]
        );

        foreach ($workingDays as $date) {
            $dateStr = $date->format('Y-m-d');

            foreach ($doctors as $doctor) {
                if ($created >= $config['target_count']) break 2;

                $wh = $workHoursCache[$doctor->id][(int)$date->format('w')] ?? null;
                if (!$wh) continue;

                for ($a = 0; $a < $config['max_per_doctor_per_day'] && $created < $config['target_count']; $a++) {
                    $slotMin = $this->findFreeSlot($doctor, $dateStr, $wh, $usedSlots);
                    if ($slotMin === null) continue;

                    $startTime = Carbon::parse("$dateStr {$wh->start_time}")
                        ->setTime(intdiv($slotMin, 60), $slotMin % 60);
                    $endTime = (clone $startTime)->addMinutes($doctor->appointment_duration ?? 30);

                    $statusPool = $startTime->isPast() ? $pastStatusPool : $futureStatusPool;
                    $status = $statusPool[array_rand($statusPool)];

                    try {
                        Appointment::create([
                            'clinic_id' => $clinic->id,
                            'doctor_id' => $doctor->id,
                            'room_id' => $doctor->room_id,
                            'patient_id' => $patients->random()->id,
                            'appointment_type_id' => $appointmentTypes->random()->id,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => $status,
                            'cancel_reason' => in_array($status, ['cancelled', 'no_show'])
                                ? $cancelReasons[array_rand($cancelReasons)]
                                : null,
                            'visit_reason' => $visitReasons[array_rand($visitReasons)],
                            'visit_in_time' => $status === 'completed' ? fake()->boolean(80) : null,
                        ]);
                        $created++;
                    } catch (UniqueConstraintViolationException) {
                        continue;
                    }
                }
            }
        }
    }

    private function findFreeSlot($doctor, string $dateStr, $wh, array &$usedSlots): ?int
    {
        $duration = $doctor->appointment_duration ?? 30;
        $whStart = Carbon::parse("$dateStr {$wh->start_time}");
        $whEnd = Carbon::parse("$dateStr {$wh->end_time}");

        $slotMinutes = [];
        $cursor = clone $whStart;
        while ((clone $cursor)->addMinutes($duration)->lte($whEnd)) {
            $slotMinutes[] = (int)$cursor->format('H') * 60 + (int)$cursor->format('i');
            $cursor->addMinutes($duration);
        }
        shuffle($slotMinutes);

        foreach ($slotMinutes as $sm) {
            $key = $doctor->id . '-' . $dateStr . '-' . $sm;
            if (!isset($usedSlots[$key])) {
                $usedSlots[$key] = true;
                return $sm;
            }
        }

        return null;
    }
}
