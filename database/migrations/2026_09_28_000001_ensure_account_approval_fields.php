<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tambahkan kolom yang belum ada
        |--------------------------------------------------------------------------
        |
        | Dibuat defensif supaya aman jika sebagian kolom sudah pernah
        | ditambahkan sebelumnya.
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table
                    ->string('email')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'account_status')) {
            Schema::table('users', function (Blueprint $table) {
                /*
                |--------------------------------------------------------------------------
                | Default active
                |--------------------------------------------------------------------------
                |
                | Akun lama tetap dapat login setelah migration.
                | Akun hasil registrasi baru akan diset "pending" dari controller.
                |--------------------------------------------------------------------------
                */
                $table
                    ->string('account_status', 20)
                    ->default('active');
            });
        }

        if (! Schema::hasColumn('users', 'approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table
                    ->timestamp('approved_at')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'approved_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table
                    ->unsignedBigInteger('approved_by')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Sengaja tidak menghapus kolom
        |--------------------------------------------------------------------------
        |
        | Migration ini dibuat sebagai "ensure migration" agar tetap aman bila
        | project sebelumnya sudah mempunyai sebagian kolom yang sama.
        |--------------------------------------------------------------------------
        */
    }
};
