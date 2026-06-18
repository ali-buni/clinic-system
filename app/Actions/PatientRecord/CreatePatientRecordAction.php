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
                foreach ($data['diseases'] as $diseaseData) {

                    if (!empty($diseaseData['id'])) {
                        $diseaseId = $diseaseData['id'];
                    } else {
                        $disease = Disease::firstOrCreate(
                            ['code' => $diseaseData['code']],
                            [
                                'ar_name'        => $diseaseData['ar_name'] ?? null,
                                'en_name'        => $diseaseData['en_name'] ?? 'Unknown',
                                'disease_nature' => $diseaseData['disease_nature'] ?? 'other',
                                'description'    => $diseaseData['description'] ?? null,
                                'is_custom'      => false
                            ]
                        );
                        $diseaseId = $disease->id;
                    }

                    $record->diseases()->sync([
                        $diseaseId => [
                            'status'   => $diseaseData['status'] ?? 'active',
                            'severity' => $diseaseData['severity'] ?? 'mild'
                        ]
                    ]);
                }
            }

            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $data['doctor_id'],
                ]);

                foreach ($data['prescription_items'] as $item) {
                    if (!empty($item['id'])) {
                        $medicineId = $item['id'];
                    } else {
                        $medicine = Medicine::firstOrCreate([
                            'api_medicine_id' => $item['api_medicine_id'] ?? null,
                            'en_name' => $item['en_name'] ?? null,
                            'ar_name' => $item['ar_name'] ?? null,
                            'generic_name_en' => $item['generic_name_en'] ?? null,
                            'generic_name_ar' => $item['generic_name_ar'] ?? null,
                            'form' => $item['form'] ?? 'tablet',
                            'strength' => $item['strength'] ?? null,
                            'is_custom' => false,
                        ]);
                        $medicineId = $medicine->id;
                    }
                    Prescription_item::create([
                        'prescription_id'     => $prescription->id,
                        'medicine_id'         => $medicineId,
                        'dosage_instruction'  => $item['dosage_instruction'] ?? null,
                        'frequency'           => $item['frequency'] ?? null,
                        'duration'            => $item['duration'] ?? null,
                    ]);
                }
            }

            // return $record->load(['diseases', 'prescriptions.items', 'doctor', 'patient']);
            return $record;
        }, attempts: 3);
    }
}
