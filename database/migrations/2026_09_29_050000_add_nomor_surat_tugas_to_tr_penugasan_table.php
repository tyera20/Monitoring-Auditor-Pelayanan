<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tr_penugasan', function (Blueprint $table) {
            $table
                ->string('nomor_surat_tugas')
                ->nullable()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('tr_penugasan', function (Blueprint $table) {
            $table->dropColumn('nomor_surat_tugas');
        });
    }
}; 