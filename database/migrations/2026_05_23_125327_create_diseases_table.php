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
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // ICD-10/SNOMED
            $table->string('ar_name');
            $table->string('en_name');
            $table->text('description')->nullable();
            $table->enum('disease_nature', [
                'infectious',
                'genetic',
                'chronic',
                'acute',
                'mental',
                'other'
            ])->default('other');
            $table->timestamps();

            $table->unique(['code'], 'uniq_disease_code');
            $table->index(['ar_name', 'en_name'], 'idx_disease_name');
            $table->boolean('is_custom')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diseases');
    }
};
