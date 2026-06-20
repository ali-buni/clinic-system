<?php

namespace App\Services;

use App\Models\PatientInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    /**
     * Get PatientInfo by ID with user relation.
     */
    public function getById(int $id): ?PatientInfo
    {
        return PatientInfo::with('user')->find($id);
    }

    /**
     * Update PatientInfo (identity fields go to User, medical fields stay).
     */
    public function updatePatientInfo(int $id, array $data): bool
    {
        $patientInfo = PatientInfo::find($id);
        if (!$patientInfo) return false;

        $userData = array_filter([
            'fname'  => $data['fname'] ?? null,
            'lname'  => $data['lname'] ?? null,
            'phone'  => $data['phone'] ?? null,
            'dob'    => $data['dob'] ?? null,
            'gender' => $data['gender'] ?? null,
        ], fn($v) => !is_null($v));

        if (!empty($userData)) {
            $patientInfo->user->update($userData);
        }

        $infoData = array_filter([
            'nationality'        => $data['nationality'] ?? null,
            'address'            => $data['address'] ?? null,
            'marital_status'     => $data['marital_status'] ?? null,
            'emergency_phone'    => $data['emergency_phone'] ?? null,
            'allergies'          => $data['allergies'] ?? null,
            'chronic_conditions' => $data['chronic_conditions'] ?? null,
            'career'             => $data['career'] ?? null,
            'blood_type'         => $data['blood_type'] ?? null,
        ], fn($v) => !is_null($v));

        return (bool) $patientInfo->update($infoData);
    }

    public function softDelete(int $id): bool
    {
        $patientInfo = PatientInfo::find($id);
        if (!$patientInfo) return false;
        return (bool) $patientInfo->delete();
    }

    public function restore(int $id): bool
    {
        $patientInfo = PatientInfo::withTrashed()->find($id);
        if (!$patientInfo || !$patientInfo->trashed()) return false;
        return $patientInfo->restore();
    }

    /**
     * Get patient medical history.
     */
    public function getPatientMedicalHistory(int $id): ?PatientInfo
    {
        return PatientInfo::with([
            'user',
            'appointments' => function ($query) {
                $query->latest('start_time');
            },
            'records' => function ($query) {
                $query->with(['prescriptions.items.medicine', 'diseases'])->latest('created_at');
            },
            'invoices' => function ($query) {
                $query->latest('created_at');
            },
        ])->find($id);
    }
}
