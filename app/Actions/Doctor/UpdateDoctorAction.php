<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class UpdateDoctorAction
{
    public function execute(int $id, array $data)
    {
        $doctor = Doctor::with('user')->find($id);
        return DB::transaction(function () use ($doctor, $data) {

            if (isset($data['specialties'])) {
                $doctor->specialties()->syncWithoutDetaching($data['specialties']);
                unset($data['specialties']);
            }
            if (isset($data['bio'])) {
                $doctor->bio = $data['bio'];
            }
            if (isset($data['consultation_fee'])) {
                $doctor->consultation_fee = $data['consultation_fee'];
            }
            if (isset($data['appointment_duration'])) {
                $doctor->appointment_duration = $data['appointment_duration'];
            }
            $doctor->save();
            if ($doctor->user) {
                $this->updateUserProfile($doctor->user, $data);
            }
            return true;
        });
    }

    /**
     * Update user profile fields.
     */
    private function updateUserProfile($user, array $data): void
    {
        $userFields = ['fname', 'lname', 'dob', 'gender'];

        foreach ($userFields as $field) {
            if (isset($data[$field])) {
                $user->$field = $data[$field];
            }
        }
        $user->save();
    }
}
