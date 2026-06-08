<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_secretary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('secretary_id')->constrained('secretaries')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['room_id', 'secretary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_secretary');
    }
};
