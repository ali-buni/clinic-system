<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicSystemSeeder extends Seeder
{
    public function run(): void
    {
        $observedModels = [
            User::class, Doctor::class, Appointment::class,
            Invoice::class, PatientInfo::class, Patient_record::class,
        ];

        $dispatchers = [];
        foreach ($observedModels as $model) {
            $dispatchers[$model] = $model::getEventDispatcher();
            $model::unsetEventDispatcher();
        }

        $this->call([
            PaymentMethodSeeder::class,
            OwnerClinicSeeder::class,
            RoomSeeder::class,
            DoctorSeeder::class,
            SecretarySeeder::class,
            WorkHourSeeder::class,
            ScheduleOverrideSeeder::class,
            PatientSeeder::class,
            ItemSeeder::class,
            AppointmentSeeder::class,
            PatientRecordSeeder::class,
            InvoiceSeeder::class,
        ]);

        foreach ($dispatchers as $model => $dispatcher) {
            if ($dispatcher !== null) {
                $model::setEventDispatcher($dispatcher);
            }
        }
    }
}
