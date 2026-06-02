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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('generic_name_ar')->nullable();
            $table->string('generic_name_en')->nullable();
            $table->string('strength')->nullable();
            $table->enum('form', ['tablet', 'capsule', 'syrup', 'injection', 'ointment'])->nullable();
            $table->timestamps();
            $table->string('api_medicine_id')->nullable()->unique();
            $table->boolean('is_custom')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
