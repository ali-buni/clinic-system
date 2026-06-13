<?php

namespace App\Services;

use App\Models\Patient_record;

class PatientRecordService
{

    public function getAllFiltered(array $filters)
    {
        $query = Patient_record::query()
            ->with([
                'diseases',
                'prescriptions.items',
                'patient',
                'doctor',
            ]);

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['clinic_id'])) {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        if (!empty($filters['disease_code'])) {
            $query->whereHas('diseases', function ($q) use ($filters) {
                $q->where(
                    'code',
                    $filters['disease_code']
                );
            });
        }

        return ModelFilter::filter(
            $query,
            $filters
        );
    }

    public function getPatientHistory(int $patientId)
    {
        return Patient_record::with(['diseases', 'prescriptions.items', 'doctor'])
            ->where('patient_id', $patientId)
            ->latest()
            ->get();
    }

    public function getRecordsByDoctor(int $patientId, int $doctorId)
    {
        return Patient_record::with(['diseases', 'prescriptions'])
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();
    }

    public function getRecordsByRoom(array $roomIds)
    {
        return Patient_record::with(['patient', 'doctor', 'diseases'])
            ->whereHas('doctor', fn($q) => $q->whereIn('room_id', $roomIds))
            ->latest()
            ->get();
    }

    public function getAllByDoctor(int $doctorId)
    {
        return Patient_record::with(['diseases', 'prescriptions.items', 'patient', 'doctor'])
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();
    }
}
