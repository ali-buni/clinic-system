<?php

namespace Database\Seeders;

use App\Models\PatientInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $clinical = require __DIR__ . '/../data/clinical_options.php';

        for ($i = 0; $i < $clinical['patient_count']; $i++) {
            $user = User::factory()->create();
            $user->assignRole('patient');
            PatientInfo::factory()->create(['user_id' => $user->id]);
        }
    }
}
