<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Disease;
use App\Models\Medicine;
use App\Models\Prescription_item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

                            activity()
                                ->performedOn($disease)
                                ->withProperties(['code' => $diseaseData['code'] ?? null, 'source' => 'record_update'])
                                ->event('created')
                                ->log('disease auto-created via record update');
                            Log::channel('structured')->info('disease auto-created via record update', [
                                'disease_id' => $disease->id, 'code' => $diseaseData['code'] ?? null,
                            ]);
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

                    $prescriptionCreated = false;
                    if (! $prescription) {
                        $prescription = Prescription::create([
                            'patient_record_id' => $record->id,
                            'doctor_id'         => $record->doctor_id,
                        ]);
                        $prescriptionCreated = true;
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

                            if ($medicine->wasRecentlyCreated) {
                                activity()
                                    ->performedOn($medicine)
                                    ->withProperties(['en_name' => $item['en_name'] ?? null, 'source' => 'record_update'])
                                    ->event('created')
                                    ->log('medicine auto-created via record update');
                                Log::channel('structured')->info('medicine auto-created via record update', [
                                    'medicine_id' => $medicine->id, 'en_name' => $item['en_name'] ?? null,
                                ]);
                            }
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
                    $deletedCount = $prescription->items()->whereNotIn('medicine_id', $syncData)->delete();

                    $eventType = $prescriptionCreated ? 'created' : 'updated';
                    activity()
                        ->performedOn($prescription)
                        ->withProperties(['patient_record_id' => $record->id, 'items_count' => count($syncData)])
                        ->event($eventType)
                        ->log($prescriptionCreated ? 'prescription created via record update' : 'prescription items updated via record update');

                    if ($prescriptionCreated) {
                        Log::channel('structured')->info('prescription created via record update', [
                            'prescription_id' => $prescription->id, 'patient_record_id' => $record->id,
                        ]);
                    } else {
                        Log::channel('structured')->info('prescription items updated via record update', [
                            'prescription_id' => $prescription->id, 'items_count' => count($syncData), 'deleted_count' => $deletedCount,
                        ]);
                    }
                }

                return $record->load(['diseases', 'prescriptions.items', 'doctor', 'patient']);
            } catch (Exception $e) {;
                throw new Exception("An error occurred while updating the record: " . $e->getMessage());
            }
        });
    }
}
