<?php

namespace App\Services;

use App\Models\Patient_record;

class PatientRecordService
{

    public function getAllFiltered(array $filters)
    {
        $query = Patient_record::with([
            'patient',
            'doctor',
        ]);
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
        return ModelFilter::filter(
            $query,
            $filters
        );
    }

    public function getPatientHistory(int $patientId)
    {
        return Patient_record::with(['doctor', 'patient',])
            ->where('patient_id', $patientId)
            // ->latest()
            ->get();
    }

    public function getRecordsByDoctor(int $patientId, int $doctorId)
    {
        return Patient_record::with(['doctor', 'patient'])
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();
    }

    public function getRecordsByRoom(array $roomIds)
    {
        return Patient_record::with(['patient', 'doctor'])
            ->whereHas('doctor', fn($q) => $q->whereIn('room_id', $roomIds))
            ->latest()
            ->get();
    }

    public function getAllByDoctor(int $doctorId)
    {
        return Patient_record::with(['doctor', 'patient'])
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();
    }

    public function show(int $id)
    {
        return Patient_record::with(['diseases', 'prescriptions.items', 'doctor', 'patient'])
            ->where('id', $id)
            ->first();
    }
}
