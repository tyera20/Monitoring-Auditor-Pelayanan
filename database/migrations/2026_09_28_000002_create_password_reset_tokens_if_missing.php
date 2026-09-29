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
        | Password Reset Tokens
        |--------------------------------------------------------------------------
        |
        | Laravel Password Broker menggunakan tabel ini untuk menyimpan token
        | reset password sementara.
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table
                    ->string('email')
                    ->primary();

                $table
                    ->string('token');

                $table
                    ->timestamp('created_at')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Sengaja no-op
        |--------------------------------------------------------------------------
        |
        | Jangan menghapus tabel bila ternyata tabel tersebut berasal dari
        | migration default Laravel yang sudah ada sebelumnya.
        |--------------------------------------------------------------------------
        */
    }
};
