<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_sakip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('satker_id')->constrained('satuan_kerja')->cascadeOnDelete();
            $table->decimal('nilai_perencanaan', 5, 2)->default(0);
            $table->decimal('nilai_pengukuran', 5, 2)->default(0);
            $table->decimal('nilai_pelaporan', 5, 2)->default(0);
            $table->decimal('nilai_evaluasi', 5, 2)->default(0);
            $table->decimal('nilai_total', 5, 2)->default(0);
            $table->enum('predikat', ['AA', 'A', 'BB', 'B', 'CC', 'C', 'D'])->default('D');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'satker_id'], 'uq_sakip_satker_tahun');
            $table->index(['tahun_anggaran_id', 'predikat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_sakip');
    }
};
