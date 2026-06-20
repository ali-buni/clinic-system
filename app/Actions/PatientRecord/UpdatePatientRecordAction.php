<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Disease;
use App\Models\Medicine;
use App\Models\Prescription_item;
use Illuminate\Support\Facades\DB;
use Exception;

class UpdatePatientRecordAction
{
    public function execute(array $data): Patient_record
    {
        return DB::transaction(function () use ($data) {
            try {
                $record = Patient_record::find($data['record_id']);

                if (!$record) {
                    throw new Exception("Patient record with ID {$data['record_id']} not found.", 404);
                }

                $record->update([
                    'diagnosis_summary' => $data['diagnosis_summary'] ?? $record->diagnosis_summary,
                    'description'       => $data['description'] ?? $record->description,
                    'status'            => $data['status'] ?? $record->status,
                    'notes'             => $data['notes'] ?? $record->notes,
                ]);

                if (isset($data['diseases']) && count($data['diseases']) > 0) {
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

                if (isset($data['prescription_items']) && count($data['prescription_items']) > 0) {
                    $prescription = $record->prescriptions()
                        ->where('patient_record_id', $record->id)
                        ->where('id', $data['preid'])
                        ->first();

                    if (! $prescription) {
                        $prescription = Prescription::create([
                            'patient_record_id' => $record->id,
                            'doctor_id'         => $record->doctor_id,
                        ]);
                    }
                    $syncData = [];

                    foreach ($data['prescription_items'] as $item) {
                        if (!empty($item['id'])) {
                            $medicineId = $item['id'];
                        } else {
                            $medicine = Medicine::firstOrCreate(
                                ['api_medicine_id' => $item['api_medicine_id']],
                                [
                                    'en_name'         => $item['en_name'] ?? null,
                                    'ar_name'         => $item['ar_name'] ?? null,
                                    'generic_name_en' => $item['generic_name_en'] ?? null,
                                    'generic_name_ar' => $item['generic_name_ar'] ?? null,
                                    'form'            => $item['form'] ?? 'tablet',
                                    'strength'        => $item['strength'] ?? null,
                                    'is_custom'       => false,
                                ]
                            );
                            $medicineId = $medicine->id;
                        }
                        $syncData[] = $medicineId;
                        $prescription->items()->updateOrCreate(
                            [
                                'medicine_id' => $medicineId
                            ],
                            [
                                'dosage_instruction' => $item['dosage_instruction'] ?? null,
                                'frequency'          => $item['frequency'] ?? null,
                                'duration'           => $item['duration'] ?? null,
                            ]
                        );
                    }
                    $prescription->items()->whereNotIn('medicine_id', $syncData)->delete();
                }

                return $record->load(['diseases', 'prescriptions.items', 'doctor', 'patient']);
            } catch (Exception $e) {;
                throw new Exception("An error occurred while updating the record: " . $e->getMessage());
            }
        });
    }
}
