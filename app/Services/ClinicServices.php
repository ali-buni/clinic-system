<?php

namespace App\Services;

use App\Events\SendMsgEvent;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class ClinicServices
{
    public function createDoctor(array $data): bool
    {
        DB::beginTransaction();
        $pwd = random_int(10000000, 99999999);
        $roomId = $data['room_id'];
        try {
            $permission = "view room " . $roomId;
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user = User::query()->create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'phone' => $data['phone'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($pwd),
            ]);
            $user->assignRole('doctor');
            $user->givePermissionTo("view room {$roomId}");

            $doctor = Doctor::query()->create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
                'room_id' => $roomId,
                'appointment_duration' => $data['appointment_duration'],
                'bio' => $data['bio'],
                'consultation_fee' => $data['consultation_fee']
            ]);

            // doctor speciality
            $doctor->specialities()->syncWithoutDetaching($data['speciality_ids']);

            event(new SendMsgEvent($user->phone, config('app.name') . ": Your password is: {$pwd}. Please change it after login."));
            logger()->info("the pwd is {$pwd} for {$user->phone}");

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("Failed to create doctor: " . $e->getMessage());
            return false;
        }
    }

    public function createSecretary(array $data): bool
    {
        DB::beginTransaction();
        $pwd = random_int(10000000, 99999999);
        $roomId = $data['room_id'];
        try {
            $permission = "view room " . $roomId;
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user = User::query()->create([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'phone' => $data['phone'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'password' => bcrypt($pwd),
            ]);
            $user->assignRole('secretary');
            $user->givePermissionTo($permission);

            Secretary::query()->create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'],
                'room_id' => $roomId,
            ]);

            event(new SendMsgEvent($user->phone, config('app.name') . ": Your password is: {$pwd}. Please change it after login."));
            logger()->info("Secretary password is {$pwd} for {$user->phone}");

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("Failed to create secretary: " . $e->getMessage());
            return false;
        }
    }

    public function updateClinicInfo(int $clinicId, array $data): bool
    {
        return DB::transaction(function () use ($clinicId, $data) {
            $clinic = Clinic::query()->find($clinicId);

            if (!$clinic) {
                return false;
            }
            $clinic->update($data);

            return true;
        });
    }

    public function getClinicInfoByOwner(int $userId): ?Clinic
    {
        return Clinic::query()
            ->with(['rooms', 'doctors.user', 'secretaries.user'])
            ->where('user_id', $userId)
            ->first();
    }

    // public function getDoctors(int $clinicId)
    // {
    //     return Doctor::query()
    //         ->where('clinic_id', $clinicId)
    //         ->with(['user', 'room'])
    //         ->get();
    // }

    // public function getDoctorById(int $id): ?Doctor
    // {
    //     return Doctor::query()
    //         ->with(['user', 'room', 'specialities'])
    //         ->find($id);
    // }

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
}
