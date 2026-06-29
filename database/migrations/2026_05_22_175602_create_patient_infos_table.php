<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->enum('marital_status', ['married', 'single', 'divorced', 'widowed', 'other'])->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->string('career')->nullable();
            $table->string('blood_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'clinic_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_infos');
    }
};
