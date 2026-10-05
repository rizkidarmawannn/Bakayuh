<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_iku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('satker_id')->constrained('satuan_kerja')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator_kinerja')->cascadeOnDelete();
            $table->decimal('nilai_target', 15, 4);
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'satker_id', 'indikator_id'], 'uq_target_iku');
            $table->index(['tahun_anggaran_id', 'satker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_iku');
    }
};
