<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DoctorSpecialtyService
{
    public function attachSpecialties(Doctor $doctor, array $specialtyIds): Collection
    {
        $doctor->specialties()->syncWithoutDetaching($specialtyIds);
        Cache::forget('specialties:all');

        LogActivityJob::dispatch('doctor', 'specialties attached', get_class($doctor), $doctor->id, null, ['specialty_ids' => $specialtyIds], 'updated');

        return $this->getCurrentSpecialties($doctor);
    }

    public function detachSpecialty(Doctor $doctor, int $specialtyId): Collection
    {
        $hasSpecialty = $doctor->specialties()->where('specialty_id', $specialtyId)->exists();

        if (! $hasSpecialty) {
            throw new \RuntimeException("Doctor does not have specialty ID {$specialtyId}");
        }
        $doctor->specialties()->detach($specialtyId);
        Cache::forget('specialties:all');

        LogActivityJob::dispatch('doctor', 'specialty detached', get_class($doctor), $doctor->id, null, ['specialty_id' => $specialtyId], 'updated');

        return $this->getCurrentSpecialties($doctor);
    }

    private function getCurrentSpecialties(Doctor $doctor): Collection
    {
        return $doctor->specialties()->get();
    }

    public function getDoctorSpecialties(int $userId): Collection
    {
        $user = User::with('doctorProfile')->find($userId);

        if (! $user || ! $user->doctorProfile) {
            throw new ModelNotFoundException('Doctor not found.');
        }

        $doctor = $user->doctorProfile;

        return $this->getCurrentSpecialties($doctor);
    }

    public function getPrimarySpecialty(Doctor $doctor)
    {
        $primary = $doctor->specialties()
            ->wherePivot('is_primary', 1)
            ->first(['id', 'en_name', 'ar_name']);

        if (! $primary) {
            throw new ModelNotFoundException('no primary specialty found');
        }

        return $primary;
    }

    public function updatePrimarySpecialty(Doctor $doctor, int $specialtyId): bool
    {
        return DB::transaction(function () use ($doctor, $specialtyId) {
            $attachedIds = $doctor->specialties()->pluck('id')->toArray();

            if (! in_array($specialtyId, $attachedIds, true)) {
                LogActivityJob::dispatch('doctor', 'primary specialty update failed - not attached', get_class($doctor), $doctor->id, null, ['specialty_id' => $specialtyId], 'updated');

                return false;
            }

            DB::table('doctor_specialty')
                ->where('doctor_id', $doctor->id)
                ->update(['is_primary' => 0]);

            $doctor->specialties()->updateExistingPivot($specialtyId, ['is_primary' => 1]);

            LogActivityJob::dispatch('doctor', 'primary specialty updated', get_class($doctor), $doctor->id, null, ['specialty_id' => $specialtyId], 'updated');

            return true;
        });
    }
}
