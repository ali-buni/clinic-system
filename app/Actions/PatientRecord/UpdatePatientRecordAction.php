<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Prescription_item;
use Illuminate\Support\Facades\DB;

class UpdatePatientRecordAction
{
    public function execute(array $data): Patient_record
    {
        return DB::transaction(function () use ($data) {

            $record = Patient_record::findOrFail($data['record_id']);

            $record->update([
                'diagnosis_summary' => $data['diagnosis_summary'] ?? $record->diagnosis_summary,
                'description'       => $data['description'] ?? $record->description,
                'status'            => $data['status'] ?? $record->status,
                'notes'             => $data['notes'] ?? $record->notes,
            ]);

            if (isset($data['updated_diseases'])) {
                $record->diseases()->sync($data['updated_diseases']);
            }

            if (!empty($data['updated_items'])) {
                $record->prescriptions()->delete();

                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $record->doctor_id,
                    'cost'              => 50.00,
                    'issued_at'         => now(),
                    'valid_until'       => now()->addMonths(3),
                ]);

                foreach ($data['updated_items'] as $item) {
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
