<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Disease;
use App\Models\Medicine;
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
                    $diseaseIds = [];
                    foreach ($data['diseases'] as $diseaseData) {
                        $disease = Disease::firstOrCreate(
                            ['code' => $diseaseData['code'] ?? null],
                            [
                                'ar_name'        => $diseaseData['ar_name'] ?? $diseaseData['name'] ?? 'غير معروف',
                                'en_name'        => $diseaseData['name'] ?? $diseaseData['en_name'] ?? 'Unknown',
                                'disease_nature' => $diseaseData['disease_nature'] ?? 'other',
                                'description'    => $diseaseData['description'] ?? 'Updated from record',
                                'is_custom'      => true
                            ]
                        );
                        $diseaseIds[] = $disease->id;
                    }
                    $record->diseases()->sync($diseaseIds);
                }

                if (isset($data['prescription_items']) && count($data['prescription_items']) > 0) {
                    $prescription = $record->prescriptions()->firstOrCreate(
                        ['patient_record_id' => $record->id],
                        [
                            'doctor_id'   => $data['doctor_id'] ?? $record->doctor_id,
                            // 'cost'        => $data['cost'] ?? 0.00,
                            // 'issued_at'   => now(),
                            'valid_until' => $data['valid_until'] ?? null,
                            'notes'       => $data['notes'] ?? null,
                        ]
                    );

                    $prescription->items()->delete();

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

                        $prescription->items()->create([
                            'medicine_id'        => $medicine->id,
                            'dosage_instruction' => $item['dosage_instruction'] ?? null,
                            'frequency'          => $item['frequency'] ?? null,
                            'duration'           => $item['duration'] ?? null,
                        ]);
                    }
                }

                return $record->load(['diseases', 'prescriptions.items']);
            } catch (Exception $e) {
                if ($e->getCode() === 404) {
                    // throw $e;
                    throw new Exception("An error occurred while updating the record: " . $e->getMessage());
                }
            }
        });
    }
}
