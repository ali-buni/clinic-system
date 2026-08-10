<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable()->after('email');
        });

        DB::transaction(function () {
            DB::table('users')
                ->whereNotNull('email')
                ->whereNull('email_hash')
                ->orderBy('id')
                ->chunkById(500, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('users')
                            ->where('id', $row->id)
                            ->update(['email_hash' => hash_hmac('sha256', strtolower(trim($row->email)), config('app.key'))]);
                    }
                });
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email_hash']);
            $table->dropColumn('email_hash');
        });
    }
};
