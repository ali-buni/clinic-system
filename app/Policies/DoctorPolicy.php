<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DoctorPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Doctor $doctor): bool
    {

        return $user->id === $doctor->user_id;
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->id === $doctor->user_id;
    }

    public function delete(User $user, Doctor $doctor): bool
    {

        return $user->clinicOwner?->id === $doctor->clinic_id;
    }

    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->clinicOwner?->id === $doctor->clinic_id;
    }
    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->clinicOwner?->id === $doctor->clinic_id;
    }
}
