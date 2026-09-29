<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pastikan Kolom Approval Tersedia
        |--------------------------------------------------------------------------
        |
        | Migration ini aman dipakai walaupun migration approval sebelumnya
        | sudah pernah dijalankan karena setiap kolom dicek terlebih dahulu.
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


        /*
        |--------------------------------------------------------------------------
        | Aktifkan Akun Lama
        |--------------------------------------------------------------------------
        |
        | Akun yang sudah ada sebelum fitur approval dianggap akun sah dan
        | harus tetap dapat login.
        |--------------------------------------------------------------------------
        */

        DB::table('users')
            ->whereNull('account_status')
            ->orWhere(
                'account_status',
                ''
            )
            ->update([
                'account_status' =>
                    'active',
            ]);
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | No-op
        |--------------------------------------------------------------------------
        |
        | Tidak menghapus kolom agar data akun yang sudah ada tetap aman.
        |--------------------------------------------------------------------------
        */
    }
};
