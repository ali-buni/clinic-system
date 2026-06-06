<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class UpdateDoctorAction
{
    public function execute(Doctor $doctor, array $data): Doctor
    {
        return DB::transaction(function () use ($doctor, $data) {

            if (isset($data['specialties'])) {
                $doctor->specialties()->sync($data['specialties']);
                unset($data['specialties']);
            }

            if (isset($data['work_hours'])) {
                unset($data['work_hours']);
            }

            $doctor->update($data);


            if (isset($data['room_id'])) {
                $doctor->room_id = $data['room_id'];
                $doctor->save();
            }

            return $doctor->fresh(['user', 'specialties', 'room']);
        });
    }
}
