<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_target_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rb_sub_indikator_id')->constrained('rb_sub_indikator')->cascadeOnDelete();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('satker_id')->constrained('satuan_kerja')->cascadeOnDelete();
            $table->enum('periode', ['B03', 'B06', 'B09', 'B12']);
            $table->dateTime('batas_waktu_upload')->nullable();
            $table->enum('status_verifikasi', ['belum_upload', 'belum_verif', 'lengkap', 'perlu_perbaikan', 'tercapai'])
                ->default('belum_upload');
            $table->text('penjelasan_zi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['rb_sub_indikator_id', 'tahun_anggaran_id', 'satker_id', 'periode'], 'uq_rb_target_periode');
            $table->index(['satker_id', 'tahun_anggaran_id', 'periode', 'status_verifikasi'], 'idx_rb_target_filter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_target_periode');
    }
};
