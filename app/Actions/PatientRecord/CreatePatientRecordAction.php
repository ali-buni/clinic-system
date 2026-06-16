<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Medicine;
use App\Models\Disease;
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
                'status'            => $data['status'] ?? 'open',
                'notes'             => $data['notes'] ?? null,
            ]);


            if (!empty($data['diseases'])) {
                $diseaseIds = [];
                foreach ($data['diseases'] as $diseaseData) {
                    $disease = Disease::firstOrCreate(
                        ['code' => $diseaseData['code']],
                        [
                            'ar_name'        => $diseaseData['ar_name'] ?? $diseaseData['name'] ?? 'Unknown',
                            'en_name'        => $diseaseData['name'] ?? $diseaseData['en_name'] ?? 'Unknown',
                            'disease_nature' => $diseaseData['disease_nature'] ?? 'other',
                            'description'    => $diseaseData['description'] ?? 'Automatically added from record',
                            'is_custom'      => true
                        ]
                    );
                    $diseaseIds[] = $disease->id;
                }
                $record->diseases()->sync($diseaseIds);
            }

            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $data['doctor_id'],
                    // 'cost'              => $data['cost'] ?? 0.00,
                    // 'issued_at'         => now(),
                    'valid_until'       => $data['valid_until'] ?? null,
                    'notes'             => $data['notes'] ?? null
                ]);

                foreach ($data['prescription_items'] as $item) {
                    $medicine = Medicine::firstOrCreate(
                        ['en_name' => $item['en_name']],
                        [
                            'ar_name'         => $item['ar_name'] ?? null,
                            'generic_name_en' => $item['generic_name_en'] ?? null,
                            'generic_name_ar' => $item['generic_name_ar'] ?? null,
                            'strength'        => $item['strength'] ?? null,
                            'form'            => $item['form'] ?? 'tablet',
                            'is_custom'       => true
                        ]
                    );
                    Prescription_item::create([
                        'prescription_id'     => $prescription->id,
                        'medicine_id'         => $medicine->id,
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
