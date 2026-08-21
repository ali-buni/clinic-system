<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->text('amount')->change();
            $table->text('stripe_refund_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->string('stripe_refund_id')->nullable()->change();
        });
    }
};
