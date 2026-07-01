<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('clinic_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained();
            $table->string('metric_name');
            $table->json('data'); 
            $table->date('snapshot_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_analytics_snapshots');
    }
};
