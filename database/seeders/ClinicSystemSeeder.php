<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Seeder;

class ClinicSystemSeeder extends Seeder
{
    public function run(): void
    {
        // // Mock ActivityLogService so UserObserver::created() doesn't crash
        // // (no authenticated user during seeding)
        // $this->container->instance(
        //     ActivityLogService::class,
        //     \Mockery::mock(ActivityLogService::class)->shouldReceive('log')->andReturnNull()->getMock()
        // );

        // // Keep User events ON so CipherSweet encrypts emails; disable others
        // $observedModels = [
        //     Doctor::class, Appointment::class,
        //     Invoice::class, PatientInfo::class, Patient_record::class,
        // ];

        // $dispatchers = [];
        // foreach ($observedModels as $model) {
        //     $dispatchers[$model] = $model::getEventDispatcher();
        //     $model::unsetEventDispatcher();
        // }

        $this->call([
            PaymentMethodSeeder::class,
            OwnerClinicSeeder::class,
            RoomSeeder::class,
            ItemSeeder::class,
            PrimaryAccountsSeeder::class,
            DoctorSeeder::class,
            SecretarySeeder::class,
            WorkHourSeeder::class,
            ScheduleOverrideSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
            PatientRecordSeeder::class,
            InvoiceSeeder::class,
        ]);

        // foreach ($dispatchers as $model => $dispatcher) {
        //     if ($dispatcher !== null) {
        //         $model::setEventDispatcher($dispatcher);
        //     }
        // }
    }
}
