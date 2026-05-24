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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->foreignId('prescription_id')->constrained('prescriptions', 'id')->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines', 'id')->nullOnDelete();

            $table->text('dosage_instruction')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->timestamps();

            $table->primary(['prescription_id', 'medicine_id'], 'pk_prescription_medicine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
