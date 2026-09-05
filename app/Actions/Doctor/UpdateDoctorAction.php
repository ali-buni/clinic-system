<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDoctorAction
{
    public function execute(int $id, array $data)
    {
        $doctor = Doctor::with('user')->find($id);
        return DB::transaction(function () use ($doctor, $data) {

            if (isset($data['specialties'])) {
                $doctor->specialties()->sync($data['specialties']);
                activity()
                    ->performedOn($doctor)
                    ->withProperties(['specialty_ids' => $data['specialties']])
                    ->event('updated')
                    ->log('doctor specialties synced');
                Log::channel('structured')->info('doctor specialties synced', [
                    'doctor_id' => $doctor->id,
                    'specialty_ids' => $data['specialties'],
                ]);
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

            activity()
                ->performedOn($doctor)
                ->withProperties(['updated_fields' => array_keys($data)])
                ->event('updated')
                ->log('doctor updated via UpdateDoctorAction');
            Log::channel('structured')->info('doctor updated via UpdateDoctorAction', [
                'doctor_id' => $doctor->id,
                'updated_fields' => array_keys($data),
            ]);

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
