<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['ar_name' => 'باطنية', 'en_name' => 'Internal Medicine'],
            ['ar_name' => 'أطفال', 'en_name' => 'Pediatrics'],
            ['ar_name' => 'نساء وتوليد', 'en_name' => 'Obstetrics and Gynecology'],
            ['ar_name' => 'جراحة عامة', 'en_name' => 'General Surgery'],
            ['ar_name' => 'قلب', 'en_name' => 'Cardiology'],
            ['ar_name' => 'جلدية', 'en_name' => 'Dermatology'],
            ['ar_name' => 'عيون', 'en_name' => 'Ophthalmology'],
            ['ar_name' => 'أذن وأنف وحنجرة', 'en_name' => 'ENT'],
            ['ar_name' => 'عظام', 'en_name' => 'Orthopedics'],
            ['ar_name' => 'مخ وأعصاب', 'en_name' => 'Neurology'],
        ];

        foreach ($specialties as $spec) {
            Specialty::firstOrCreate(
                ['ar_name' => $spec['ar_name']],
                $spec
            );
        }

        echo "✅ تم إضافة " . count($specialties) . " تخصص طبي بنجاح!\n";
    }
}
