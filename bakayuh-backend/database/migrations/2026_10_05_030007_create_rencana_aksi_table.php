<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_aksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('satker_id')->constrained('satuan_kerja')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator_kinerja')->cascadeOnDelete();
            $table->string('nama_aksi');
            $table->enum('triwulan', ['TW1', 'TW2', 'TW3', 'TW4']);
            $table->text('target_output')->nullable();
            $table->timestamps();

            $table->index(['satker_id', 'tahun_anggaran_id', 'triwulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_aksi');
    }
};
