<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, Doctor $doctor): bool
    {
        return $user->id === $doctor->user_id ||
               $user->clinicOwner?->id === $doctor->clinic_id;
    }

    public function create(User $user): bool
    {
        return $user->clinicOwner?->id !== null;
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->id === $doctor->user_id;
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        $isDoctor = ($user->id === $doctor->user_id);
        $isClinicOwner      = ($user->clinicOwner?->id === $doctor->clinic_id);

        return $isDoctor || $isClinicOwner;
    }
}
