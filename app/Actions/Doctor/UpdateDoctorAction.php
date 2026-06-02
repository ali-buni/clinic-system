<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class UpdateDoctorAction
{
    public function execute(Doctor $doctor, array $data): Doctor
    {
        DB::beginTransaction();
        try {
            if ($userData = array_intersect_key($data, array_flip(['fname', 'lname', 'phone', 'dob', 'gender']))) {
                $doctor->user->update($userData);
            }

            $doctorData = array_intersect_key($data, array_flip([
                'room_id', 'appointment_duration', 'bio', 'consultation_fee'
            ]));

            $doctor->update($doctorData);

            DB::commit();

            return $doctor->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
