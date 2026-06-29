<?php

namespace App\Services;

use App\Models\Doctor;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorSpecialtyService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function attachSpecialties(Doctor $doctor, array $specialtyIds): Collection
    {
        $doctor->specialties()->syncWithoutDetaching($specialtyIds);
        Cache::forget('specialties:all');

        $this->activityLog->log('doctor', 'specialties attached', $doctor, null, ['specialty_ids' => $specialtyIds], 'updated');
        Log::channel('structured')->info('specialties attached to doctor', [
            'doctor_id' => $doctor->id, 'specialty_ids' => $specialtyIds,
        ]);

        return $this->getCurrentSpecialties($doctor);
    }

    public function detachSpecialty(Doctor $doctor, int $specialtyId): Collection
    {
        $hasSpecialty = $doctor->specialties()->where('specialty_id', $specialtyId)->exists();

        if (!$hasSpecialty) {
            throw new \RuntimeException("Doctor does not have specialty ID {$specialtyId}");
        }
        $doctor->specialties()->detach($specialtyId);
        Cache::forget('specialties:all');

        $this->activityLog->log('doctor', 'specialty detached', $doctor, null, ['specialty_id' => $specialtyId], 'updated');
        Log::channel('structured')->info('specialty detached from doctor', [
            'doctor_id' => $doctor->id, 'specialty_id' => $specialtyId,
        ]);

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
                $this->activityLog->log('doctor', 'primary specialty update failed - not attached', $doctor, null, ['specialty_id' => $specialtyId], 'updated');
                Log::channel('structured')->warning('updatePrimarySpecialty - specialty not attached', [
                    'doctor_id' => $doctor->id, 'specialty_id' => $specialtyId,
                ]);
                return false;
            }

            DB::table('doctor_specialty')
                ->where('doctor_id', $doctor->id)
                ->update(['is_primary' => 0]);

            $doctor->specialties()->updateExistingPivot($specialtyId, ['is_primary' => 1]);

            $this->activityLog->log('doctor', 'primary specialty updated', $doctor, null, ['specialty_id' => $specialtyId], 'updated');
            Log::channel('structured')->info('primary specialty updated', [
                'doctor_id' => $doctor->id, 'specialty_id' => $specialtyId,
            ]);

            return true;
        });
    }
}
