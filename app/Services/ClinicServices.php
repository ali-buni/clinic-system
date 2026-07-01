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
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}
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

            $user = User::create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'email' => $data['email'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($temporaryPassword),
            ]);

            $user->assignRole('doctor');
            PermissionHelper::grantRoomPermission($user, $roomId);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
                'room_id' => $roomId,
                'appointment_duration' => $data['appointment_duration'],
                'bio' => $data['bio'] ?? null,
                'consultation_fee' => $data['consultation_fee'],
            ]);

            $doctor->specialties()->syncWithoutDetaching($data['specialty_ids']);

            if ($user->phone) {
                try {
                    event(new SendMsgEvent(
                        $user->phone,
                        config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
                    ));
                } catch (\Exception $e) {
                    throw new RuntimeException('Failed to send SMS: ' . $e->getMessage());
                }
            }

            $this->activityLog->log(
                'clinic', 'created doctor account', $doctor, null, [
                    'clinic_id' => $data['clinic_id'],
                ], 'created'
            );
            Log::channel('structured')->info('doctor account created', [
                'user_id' => $user->id, 'doctor_id' => $doctor->id, 'clinic_id' => $data['clinic_id'],
            ]);

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

            $user = User::create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'email' => $data['email'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($temporaryPassword),
            ]);

            $user->assignRole('secretary');

            $secretary = Secretary::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
            ]);

            $roomIds = array_values(array_filter(array_map('intval', $roomIds)));
            if (!empty($roomIds)) {
                $secretary->rooms()->sync($roomIds);
                foreach ($roomIds as $rId) {
                    PermissionHelper::grantRoomPermission($user, $rId);
                }
            }
            if ($user->phone) {
                try {
                    event(new SendMsgEvent(
                        $user->phone,
                        config('app.name') . ": Your password is: {$temporaryPassword}. Please change it after login."
                    ));
                } catch (\Exception $e) {
                    throw new RuntimeException('Failed to send SMS: ' . $e->getMessage());
                }
            }

            $this->activityLog->log(
                'clinic', 'created secretary account', $secretary, null, [
                    'clinic_id' => $data['clinic_id'],
                ], 'created'
            );
            Log::channel('structured')->info('secretary account created', [
                'user_id' => $user->id, 'secretary_id' => $secretary->id, 'clinic_id' => $data['clinic_id'],
            ]);

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
                Log::channel('structured')->warning('updateClinicInfo - clinic not found', ['clinic_id' => $clinicId]);
                return false;
            }

            $result = $clinic->update($data);

            $this->activityLog->log(
                'clinic', 'updated clinic info', $clinic, null, [
                    'updated_fields' => array_keys($data),
                ], 'updated'
            );
            Log::channel('structured')->info('clinic info updated', [
                'clinic_id' => $clinicId, 'updated_fields' => array_keys($data),
            ]);

            return $result;
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
