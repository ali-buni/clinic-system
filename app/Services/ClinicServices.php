<?php

namespace App\Services;

use App\Events\SendMsgEvent;
use App\Helpers\PermissionHelper;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClinicServices
{
    /**
     * Create a new doctor account with associated permissions.
     *
     * @param array $data Doctor creation data with password generated
     * @return bool Success indicator
     */
    public function createDoctor(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $temporaryPassword = random_int(10000000, 99999999);
            $roomId = $data['room_id'];

            // Create user account
            $user = User::create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'phone' => $data['phone'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($temporaryPassword),
            ]);

            // Assign role and permissions
            $user->assignRole('doctor');
            PermissionHelper::grantRoomPermission($user, $roomId);

            // Create doctor profile
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
                'room_id' => $roomId,
                'appointment_duration' => $data['appointment_duration'],
                'bio' => $data['bio'],
                'consultation_fee' => $data['consultation_fee'],
            ]);

            // Assign specialties
            $doctor->specialities()->syncWithoutDetaching($data['speciality_ids']);

            // Send credential via SMS
            event(new SendMsgEvent(
                $user->phone,
                config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
            ));

            return true;
        }, attempts: 3);
    }

    /**
     * Create a new secretary account with associated permissions.
     *
     * @param array $data Secretary creation data with password generated
     * @return bool Success indicator
     */
    public function createSecretary(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $temporaryPassword = random_int(10000000, 99999999);
            $roomId = $data['room_id'];

            // Create user account
            $user = User::create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'phone' => $data['phone'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($temporaryPassword),
            ]);

            // Assign role and permissions
            $user->assignRole('secretary');
            PermissionHelper::grantRoomPermission($user, $roomId);

            // Create secretary profile
            Secretary::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
                'room_id' => $roomId,
            ]);

            // Send credential via SMS
            event(new SendMsgEvent(
                $user->phone,
                config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
            ));

            return true;
        }, attempts: 3);
    }

    /**
     * Update clinic information.
     *
     * @param int $clinicId
     * @param array $data
     * @return bool Success indicator
     */
    public function updateClinicInfo(int $clinicId, array $data): bool
    {
        return DB::transaction(function () use ($clinicId, $data) {
            $clinic = Clinic::find($clinicId);

            if (!$clinic) {
                return false;
            }

            return $clinic->update($data);
        }, attempts: 3);
    }

    /**
     * Get clinic information with eager-loaded relationships.
     *
     * @param int $userId
     * @return Clinic|null
     */
    public function getClinicInfoByOwner(int $userId): ?Clinic
    {
        return Clinic::query()
            ->with(['rooms', 'doctors.user', 'secretaries.user'])
            ->where('user_id', $userId)
            ->first();
    }
}

    // public function getSecretaries(int $clinicId)
    // {
    //     return Secretary::query()
    //         ->where('clinic_id', $clinicId)
    //         ->with(['user', 'room'])
    //         ->get();
    // }

    // public function getSecretaryById(int $id): ?Secretary
    // {
    //     return Secretary::query()
    //         ->with(['user', 'room'])
    //         ->find($id);
    // }

    // public function deleteDoctor(int $id): bool
    // {
    //     return (bool) Doctor::query()->where('id', $id)->softDeletes();
    // }

    // public function deleteSecretary(int $id): bool
    // {
    //     return (bool) Secretary::query()->where('id', $id)->softDeletes();
    // }

    // public function restoreDoctor(int $id): bool
    // {
    //     return (bool) Doctor::query()->withTrashed()->where('id', $id)->restore();
    // }

    // public function restoreSecretary(int $id): bool
    // {
    //     return (bool) Secretary::query()->withTrashed()->where('id', $id)->restore();
    // }
