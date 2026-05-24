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
        Schema::create('patient_record_disease', function (Blueprint $table) {
            $table->foreignId('patient_record_id')->constrained('patient_records')->cascadeOnDelete();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
            $table->enum('status', ['active', 'resolved', 'chronic'])->default('active');
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('mild');
            $table->timestamps();

            $table->primary(['patient_record_id', 'disease_id'], 'pk_record_disease');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_record_disease');
    }
};
