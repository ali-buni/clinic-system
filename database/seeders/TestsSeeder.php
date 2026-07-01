<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Disease;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use App\Models\Room;
use App\Models\Schedule_override;
use App\Models\Secretary;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Work_hour;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SpecialtySeeder::class,
            AppointmentTypesSeeder::class,
            RolesAndPermissionsSeeder::class,
            MedicineSeeder::class,
            DiseaseSeeder::class,
        ]);

        $owner = User::firstOrCreate(
            ['phone' => '0999999999'],
            ['email' => 'owner@clinic.test', 'fname' => 'Owner', 'lname' => 'User', 'gender' => 'male', 'password' => bcrypt('password')]
        );
        $owner->assignRole('owner');

        $clinic = Clinic::firstOrCreate(
            ['user_id' => $owner->id],
            ['title' => 'Test Clinic', 'location' => 'Test Location', 'phone' => '0911111111']
        );

        foreach (range(1, 5) as $i) {
            Room::firstOrCreate(['clinic_id' => $clinic->id, 'name' => "Room $i"]);
        }
        $rooms = Room::where('clinic_id', $clinic->id)->get();

        $doctorUser = User::firstOrCreate(
            ['phone' => '0951232301'],
            ['email' => 'doctor0@clinic.test', 'fname' => 'Test', 'lname' => 'Doctor', 'gender' => 'male', 'password' => bcrypt('password')]
        );
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id, 'clinic_id' => $clinic->id],
            ['room_id' => $rooms->first()->id, 'appointment_duration' => 30, 'consultation_fee' => 200]
        );
        $doctor->specialties()->sync([Specialty::first()->id => ['is_primary' => true]]);

        $secUser = User::firstOrCreate(
            ['phone' => '0900000001'],
            ['email' => 'secretary0@clinic.test', 'fname' => 'Test', 'lname' => 'Secretary', 'gender' => 'female', 'password' => bcrypt('password')]
        );
        $secUser->assignRole('secretary');
        $secretary = Secretary::firstOrCreate(
            ['user_id' => $secUser->id, 'clinic_id' => $clinic->id],
            []
        );
        $secretary->rooms()->sync([$rooms->first()->id]);

        $patientUser = User::firstOrCreate(
            ['phone' => '0988888888'],
            ['email' => 'patient@clinic.test', 'fname' => 'Test', 'lname' => 'Patient', 'gender' => 'male', 'password' => bcrypt('password')]
        );
        $patientUser->assignRole('patient');
        $patient = PatientInfo::firstOrCreate(
            ['user_id' => $patientUser->id],
            ['clinic_id' => $clinic->id, 'dob' => '1990-01-01', 'gender' => 'male']
        );

        $patientUser2 = User::firstOrCreate(
            ['phone' => '0988888887'],
            ['email' => 'patient2@clinic.test', 'fname' => 'Second', 'lname' => 'Patient', 'gender' => 'female', 'password' => bcrypt('password')]
        );
        $patientUser2->assignRole('patient');
        PatientInfo::firstOrCreate(
            ['user_id' => $patientUser2->id],
            ['clinic_id' => $clinic->id, 'dob' => '1995-05-15', 'gender' => 'female']
        );

        $apptType = Appointment_type::first();
        $medicine = Medicine::first();
        $disease = Disease::first();

        $shifts = [
            0 => ['start' => '09:00', 'end' => '17:00'],
            1 => ['start' => '09:00', 'end' => '17:00'],
            2 => ['start' => '09:00', 'end' => '17:00'],
            3 => ['start' => '09:00', 'end' => '17:00'],
            4 => ['start' => '09:00', 'end' => '17:00'],
        ];

        foreach ([$doctor] as $doc) {
            foreach ($shifts as $dayIndex => $shift) {
                Work_hour::firstOrCreate(
                    ['doctor_id' => $doc->id, 'day_of_week' => $dayIndex],
                    ['start_time' => $shift['start'], 'end_time' => $shift['end'], 'max_patients_per_day' => 10, 'is_active' => true]
                );
            }
        }

        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'room_id' => $doctor->room_id,
            'patient_id' => $patient->id,
            'appointment_type_id' => $apptType->id,
            'start_time' => Carbon::now()->addDays(1)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(1)->setTime(10, 30),
            'status' => 'scheduled',
            'visit_reason' => 'Test appointment',
        ]);

        $record = Patient_record::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
            'description' => 'Test description',
            'status' => 'closed',
            'notes' => 'Test notes',
        ]);
        $record->diseases()->attach($disease->id, ['status' => 'active', 'severity' => 'mild']);

        Schedule_override::create([
            'doctor_id' => $doctor->id,
            'override_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'override_type' => 'closed',
            'reason' => 'Training',
            'is_closed' => true,
        ]);
    }
}
