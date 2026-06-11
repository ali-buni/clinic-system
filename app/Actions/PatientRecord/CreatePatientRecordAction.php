<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Prescription_item;
use Illuminate\Support\Facades\DB;

class CreatePatientRecordAction
{
    public function execute(array $data): Patient_record
    {
        return DB::transaction(function () use ($data) {

            $record = Patient_record::create([
                'patient_id'        => $data['patient_id'],
                'doctor_id'         => $data['doctor_id'],
                'clinic_id'         => $data['clinic_id'],
                'appointment_id'    => $data['appointment_id'],
                'diagnosis_summary' => $data['diagnosis_summary'],
                'description'       => $data['description'] ?? null,
                'status'            => $data['status'] ?? 'active',
                'notes'             => $data['notes'] ?? null,
            ]);

            if (!empty($data['diseases'])) {
                $record->diseases()->sync($data['diseases']);
            }

            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $data['doctor_id'],
                    'cost'              => 50.00,
                    'issued_at'         => now(),
                    'valid_until'       => now()->addMonths(3),
                ]);

                foreach ($data['prescription_items'] as $item) {
                    Prescription_item::create([
                        'prescription_id'     => $prescription->id,
                        'medicine_id'         => $item['medicine_id'],
                        'dosage_instruction'  => $item['dosage_instruction'] ?? null,
                        'frequency'           => $item['frequency'] ?? null,
                        'duration'            => $item['duration'] ?? null,
                    ]);
                }
            }

            return $record->load(['diseases', 'prescriptions.items']);
        });
    }
}
