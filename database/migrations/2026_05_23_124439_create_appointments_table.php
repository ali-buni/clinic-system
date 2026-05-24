<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_type_id')->nullable()->constrained('appointment_types')->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])
                ->default('scheduled');
            $table->text('cancel_reason')->nullable();
            $table->text('visit_reason')->nullable();
            $table->boolean('visit_in_time')->nullable();
            $table->boolean('requires_followup')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'doctor_id', 'start_time'], 'idx_appt_clinic_doctor_start');
            $table->index(['patient_id', 'start_time'], 'idx_appt_patient_start');

            $table->unique(['clinic_id', 'doctor_id', 'start_time'], 'uniq_appt_clinic_doctor_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
