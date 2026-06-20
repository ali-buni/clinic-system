<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patient_infos')->cascadeOnDelete();
        });

        Schema::table('patient_records', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patient_infos')->cascadeOnDelete();
        });

        Schema::table('invoices', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patient_infos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('patient_records', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('invoices', function ($table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->nullOnDelete();
        });
    }
};
