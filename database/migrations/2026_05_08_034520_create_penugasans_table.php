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
        Schema::create('tr_penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('ms_layanan')->cascadeOnDelete();
            $table->text('task_detail');
            $table->string('tempat');
            $table->string('komoditi');
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_selesai')->index();
            $table->timestamps();

            $table->index(['layanan_id', 'tanggal_mulai']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_penugasan');
    }
};
