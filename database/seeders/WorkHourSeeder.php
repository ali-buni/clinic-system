<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Work_hour;
use Illuminate\Database\Seeder;

class WorkHourSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = require __DIR__ . '/../data/work_shifts.php';
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            foreach ($shifts as $dayIndex => $shift) {
                Work_hour::firstOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $dayIndex],
                    [
                        'start_time' => $shift['start'],
                        'end_time' => $shift['end'],
                        'max_patients_per_day' => fake()->numberBetween(10, 20),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
