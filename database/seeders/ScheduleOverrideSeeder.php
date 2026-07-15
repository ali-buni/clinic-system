<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule_override;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleOverrideSeeder extends Seeder
{
    public function run(): void
    {
        $overrideReasons = require __DIR__ . '/../data/schedule_overrides.php';
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            foreach ($overrideReasons as $override) {
                $date = Carbon::now()->addDays(mt_rand(1, 60));
                Schedule_override::firstOrCreate(
                    ['doctor_id' => $doctor->id, 'override_date' => $date->format('Y-m-d')],
                    array_merge($override, [
                        'doctor_id' => $doctor->id,
                        'override_date' => $date->format('Y-m-d'),
                        'override_type' => $override['is_closed'] ? 'closed' : 'time_change',
                    ])
                );
            }
        }
    }
}
