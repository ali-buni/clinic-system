<?php
namespace App\Services;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DoctorSpecialtyService
{
    public function attachSpecialties(Doctor $doctor, array $specialtyIds): Collection
    {
        $doctor->specialties()->syncWithoutDetaching($specialtyIds);
        return $this->getCurrentSpecialties($doctor);
    }

    public function syncSpecialties(Doctor $doctor, array $specialtyIds): Collection
    {
        $doctor->specialties()->sync($specialtyIds);
        return $this->getCurrentSpecialties($doctor);
    }

    public function detachSpecialty(Doctor $doctor, int $specialtyId): Collection
    {
        $doctor->specialties()->detach($specialtyId);
        return $this->getCurrentSpecialties($doctor);
    }

    private function getCurrentSpecialties(Doctor $doctor): Collection
    {
        return $doctor->specialties()->get(['id', 'en_name', 'ar_name']);
    }

    public function getDoctorSpecialties($userId)
    {
        $user = User::with('doctorProfile')->find($userId);

        if (! $user || ! $user->doctorProfile) {
            return response()->json([
                'status' => false,
                'message' => 'Doctor not found.',
            ], 404);
        }

        $doctor = $user->doctorProfile;
        return $this->getCurrentSpecialties($doctor);
    }


    public function getPrimarySpecialty(Doctor $doctor)
    {
        return $doctor->specialties()
            ->wherePivot('is_primary', 1)
            ->first(['id', 'en_name', 'ar_name']);
    }

    public function updatePrimarySpecialty(Doctor $doctor, $specialtyId): bool
    {
        return DB::transaction(function () use ($doctor, $specialtyId) {

            $doctor->specialties()->updateExistingPivot($doctor->specialties()->pluck('id'), [
                'is_primary' => 0
            ]);

            if ($doctor->specialties()->where('specialty_id', $specialtyId)->exists()) {
                $doctor->specialties()->updateExistingPivot($specialtyId, [
                    'is_primary' => 1
                ]);
                return true;
            }

            return false;
        });
    }
}