<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_iku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_iku_id')->unique()->constrained('target_iku')->cascadeOnDelete();
            $table->decimal('nilai_realisasi', 15, 4);
            $table->decimal('persentase_capaian', 8, 4)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('persentase_capaian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_iku');
    }
};
