<?php

namespace App\Services;

use App\Events\SendMsgEvent;
use App\Helpers\PermissionHelper;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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
            $doctor->specialties()->syncWithoutDetaching($data['specialty_ids']);

            try {
                // Send credential via SMS
                event(new SendMsgEvent(
                    $user->phone,
                    config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
                ));
            } catch (\Exception $e) {
                throw new RuntimeException('Failed to send SMS: ' . $e->getMessage());
            }
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
            $roomIds = $data['room_ids'];

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

            // Create secretary profile
            $secretary = Secretary::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
            ]);

            // Attach rooms and grant permissions
            $roomIds = array_values(array_filter(array_map('intval', $roomIds)));
            if (!empty($roomIds)) {
                $secretary->rooms()->sync($roomIds);
                foreach ($roomIds as $rId) {
                    PermissionHelper::grantRoomPermission($user, $rId);
                }
            }
            // Send credential via SMS (synchronous)
            try {
                // event(new SendMsgEvent(
                //     $user->phone,
                //     config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
                // ));
                Log::info("SMS to {$user->phone}: Your password is: {$temporaryPassword}. Please change it after login.");
            } catch (\Exception $e) {
                throw new RuntimeException('Failed to send SMS: ' . $e->getMessage());
            }

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
