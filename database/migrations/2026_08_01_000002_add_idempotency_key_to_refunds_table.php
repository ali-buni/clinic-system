<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stable key derived from (payment_id, amount) so that retried refund jobs
     * reuse the same Stripe idempotency key instead of issuing duplicate refunds,
     * and so an existing refund can be detected before hitting the gateway.
     * Stored in plaintext (not CipherSweet-encrypted) so it can be queried.
     */
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('idempotency_key', 64)
                ->nullable()
                ->after('stripe_refund_id');
            $table->unique('idempotency_key', 'uniq_refunds_idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropUnique('uniq_refunds_idempotency_key');
            $table->dropColumn('idempotency_key');
        });
    }
};
