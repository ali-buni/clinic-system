<?php

namespace Database\Seeders;

use App\Models\Appointment_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointmentTypes = collect([
            ['types' => 1, 'ar_name' => 'استشارة عامة', 'en_name' => 'General Consultation'],
            ['types' => 1, 'ar_name' => 'متابعة 1', 'en_name' => 'Follow Up 1'],
            ['types' => 2, 'ar_name' => 'متابعة 2', 'en_name' => 'Follow Up 2'],
            ['types' => 3, 'ar_name' => 'متابعة 3', 'en_name' => 'Follow Up 3'],
            ['types' => 1, 'ar_name' => 'طوارئ', 'en_name' => 'Emergency'],
            ['types' => 1, 'ar_name' => 'فحص', 'en_name' => 'Examination'],
            ['types' => 1, 'ar_name' => 'جلسة قصيرة', 'en_name' => 'Short Session'],
            ['types' => 2, 'ar_name' => 'جلسة متوسطة', 'en_name' => 'Medium Session'],
            ['types' => 3, 'ar_name' => 'جلسة طويلة', 'en_name' => 'Long Session'],
            ['types' => 1, 'ar_name' => 'مراجعة', 'en_name' => 'Review'],
        ])->map(fn(array $data) => Appointment_type::firstOrCreate($data));
    }
}
