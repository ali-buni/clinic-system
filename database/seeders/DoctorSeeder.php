<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctorData = require __DIR__ . '/../data/doctors.php';
        $clinic = Clinic::first();
        if (!$clinic) return;

        $rooms = Room::where('clinic_id', $clinic->id)->get()->values();

        foreach ($doctorData as $index => $data) {
            $phone = '09512323' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'email' => "doctor{$index}@clinic.test",
                    'fname' => $data['fname'],
                    'lname' => $data['lname'],
                    'gender' => $data['gender'],
                    'password' => bcrypt('password'),
                ]
            );
            $user->assignRole('doctor');

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id, 'clinic_id' => $clinic->id],
                [
                    'room_id' => $rooms->get($index % $rooms->count())->id,
                    'appointment_duration' => 30,
                    'bio' => $data['bio'],
                    'consultation_fee' => $data['consultation_fee'],
                ]
            );

            $specialty = Specialty::where('en_name', $data['specialty'])->first();
            if ($specialty) {
                $doctor->specialties()->sync([$specialty->id => ['is_primary' => true]]);
            }
        }
    }
}
