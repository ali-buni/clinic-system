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
                $doctor->specialties()->syncWithoutDetaching($data['specialties']);
                unset($data['specialties']);
            }
            $doctor->update($data);

            return $doctor->fresh(['user', 'specialties', 'room']);
        });
    }
}
