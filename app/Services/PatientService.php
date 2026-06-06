<?php

namespace App\Services;

use App\Models\Patient;

class PatientService
{
    /**
     * Create a new patient.
     *
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    /**
     * Get a patient by ID with eager-loaded relationships.
     *
     * @param int $id
     * @return Patient|null
     */
    public function getById(int $id): ?Patient
    {
        return Patient::find($id);
    }

    /**
     * Update patient information.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return (bool) Patient::where('id', $id)->update($data);
    }

    /**
     * Soft delete a patient.
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool
    {
        return (bool) Patient::where('id', $id)->delete();
    }

    /**
     * Restore a soft-deleted patient.
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        $patient = Patient::withTrashed()->find($id);

        if (!$patient || !$patient->trashed()) {
            return false;
        }

        return $patient->restore();
    }

    /**
     * Get patient information with full medical history.
     *
     * @param int $id
     * @return Patient|null
     */
    public function getPatientMedicalHistory(int $id): ?Patient
    {
        return Patient::with([
            'appointments' => function ($query) {
                $query->latest('start_time');
            },
            'records' => function ($query) {
                $query->latest('created_at');
            },
            'prescriptions' => function ($query) {
                $query->latest('issued_at');
            },
            'invoices' => function ($query) {
                $query->latest('created_at');
            },
        ])->find($id);
    }
}
