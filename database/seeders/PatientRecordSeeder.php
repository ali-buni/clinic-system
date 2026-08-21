<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Disease;
use App\Models\Medicine;
use App\Models\Patient_record;
use App\Models\Prescription;
use App\Models\Prescription_item;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientRecordSeeder extends Seeder
{
    public function run(): void
    {
        $maps = require __DIR__ . '/../data/medicine_disease_maps.php';
        $prescriptionOpts = require __DIR__ . '/../data/prescription_options.php';
        $clinical = require __DIR__ . '/../data/clinical_options.php';

        $clinic = Clinic::first();
        if (!$clinic) return;

        $appointments = Appointment::all();
        $diseases = Disease::all();
        $medicines = Medicine::all();

        foreach ($appointments as $i => $appointment) {
            if ($appointment->record()->exists()) continue;

            if (in_array($appointment->status, ['scheduled', 'confirmed'])) {
                Patient_record::create([
                    'clinic_id' => $clinic->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'appointment_id' => $appointment->id,
                    'diagnosis_summary' => null,
                    'description' => 'Scheduled visit - pending examination.',
                    'status' => 'open',
                    'notes' => null,
                ]);
                continue;
            }

            if ($appointment->status !== 'completed') continue;

            $record = Patient_record::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'diagnosis_summary' => fake()->sentence(4),
                'description' => fake()->paragraph(3),
                'status' => 'closed',
                'notes' => fake()->paragraph(2),
            ]);

            $numDiseases = fake()->numberBetween(1, 3);
            $recordDiseases = $diseases->random($numDiseases);
            foreach ($recordDiseases as $disease) {
                $record->diseases()->attach($disease->id, [
                    'status' => fake()->randomElement($clinical['disease_statuses']),
                    'severity' => fake()->randomElement($clinical['severities']),
                ]);
            }

            $prescribableMeds = $medicines->filter(
                fn($m) => $m->id !== $clinical['unprescribable_medicine_id']
            );
            $selectedMeds = $prescribableMeds->random(fake()->numberBetween(1, 3));

            $prescription = Prescription::create([
                'patient_record_id' => $record->id,
                'doctor_id' => $appointment->doctor_id,
                'valid_until' => Carbon::parse($appointment->start_time)->addDays(fake()->numberBetween(7, 90)),
                'issued_at' => $appointment->start_time,
                'cost' => fake()->randomFloat(2, 20, 200),
                'notes' => fake()->sentence(),
            ]);

            foreach ($selectedMeds as $medicine) {
                $isGood = $i >= $clinical['mismatch_record_count'];
                if (!$isGood) {
                    $badDiseaseIds = $maps['unsuitable'][$medicine->id] ?? [];
                    $hasBadMatch = !empty(array_intersect(
                        $recordDiseases->pluck('id')->toArray(),
                        $badDiseaseIds
                    ));
                    $isGood = !$hasBadMatch;
                }
                if ($isGood) {
                    Prescription_item::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $medicine->id,
                        'dosage_instruction' => fake()->randomElement($prescriptionOpts['dosage_instructions']),
                        'frequency' => fake()->randomElement($prescriptionOpts['frequencies']),
                        'duration' => fake()->randomElement($prescriptionOpts['durations']),
                    ]);
                }
            }
        }
    }
}
