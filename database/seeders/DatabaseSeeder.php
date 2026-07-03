<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SpecialtySeeder::class,
            AppointmentTypesSeeder::class,
            RolesAndPermissionsSeeder::class,
            MedicineSeeder::class,
            DiseaseSeeder::class,
            ClinicSystemSeeder::class,
            AdminUserSeeder::class,
            AuthTokenSeeder::class,
        ]);
    }
}
