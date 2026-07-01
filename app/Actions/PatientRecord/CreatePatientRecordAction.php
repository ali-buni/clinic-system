<?php

namespace App\Actions\PatientRecord;

use App\Models\Patient_record;
use App\Models\Medicine;
use App\Models\Disease;
use App\Models\Prescription;
use App\Models\Prescription_item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

                        activity()
                            ->performedOn($disease)
                            ->withProperties(['code' => $diseaseData['code'] ?? null, 'source' => 'patient_record_create'])
                            ->event('created')
                            ->log('disease auto-created via patient record');
                        Log::channel('structured')->info('disease auto-created via patient record', [
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

            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $data['doctor_id'],
                ]);

                activity()
                    ->performedOn($prescription)
                    ->withProperties(['patient_record_id' => $record->id])
                    ->event('created')
                    ->log('prescription created via patient record');
                Log::channel('structured')->info('prescription created via patient record', [
                    'prescription_id' => $prescription->id, 'patient_record_id' => $record->id,
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

                        if ($medicine->wasRecentlyCreated) {
                            activity()
                                ->performedOn($medicine)
                                ->withProperties(['en_name' => $item['en_name'] ?? null, 'source' => 'prescription_create'])
                                ->event('created')
                                ->log('medicine auto-created via prescription');
                            Log::channel('structured')->info('medicine auto-created via prescription', [
                                'medicine_id' => $medicine->id, 'en_name' => $item['en_name'] ?? null,
                            ]);
                        }
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
