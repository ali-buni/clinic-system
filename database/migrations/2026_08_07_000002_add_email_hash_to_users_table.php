<?php

use App\Models\User;
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
            foreach (User::withTrashed()->cursor() as $user) {
                if (! $user->email || $user->email_hash) {
                    continue;
                }

                $user->withoutEvents(function () use ($user) {
                    $user->forceFill(['email_hash' => User::hashEmail($user->email)])->save();
                });
            }

            $duplicates = DB::table('users')
                ->select('email_hash', DB::raw('MIN(id) as keep_id'))
                ->whereNotNull('email_hash')
                ->groupBy('email_hash')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('users')
                    ->where('email_hash', $dup->email_hash)
                    ->where('id', '!=', $dup->keep_id)
                    ->update(['email' => null, 'email_hash' => null]);
            }
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
