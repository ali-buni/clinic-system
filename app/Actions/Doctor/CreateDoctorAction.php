<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDoctorAction
{
    public function execute(array $data, int $clinicId): Doctor
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'fname'=> $data['fname'],
                'lname'=> $data['lname'],
                'phone'=> $data['phone'],
                'password'=> Hash::make($data['password']),
                'dob'=> $data['dob'] ?? null,
                'gender'=> $data['gender'] ?? null,
            ]);

            $user->assignRole('doctor');

            $doctor = Doctor::create([
                'user_id'=> $user->id,
                'clinic_id'=> $clinicId,
                'room_id'=> $data['room_id'] ?? null,
                'appointment_duration'=> $data['appointment_duration'] ?? 30,
                'bio'=> $data['bio'] ?? null,
                'consultation_fee'=> $data['consultation_fee'],
            ]);

            DB::commit();

            return $doctor;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
    }
    }
}
