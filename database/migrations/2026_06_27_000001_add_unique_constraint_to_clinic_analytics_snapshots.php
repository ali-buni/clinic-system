<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_analytics_snapshots', function (Blueprint $table) {
            $table->unique(['clinic_id', 'metric_name', 'snapshot_date'], 'unique_clinic_metric_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_analytics_snapshots', function (Blueprint $table) {
            $table->dropUnique('unique_clinic_metric_per_day');
        });
    }
};
