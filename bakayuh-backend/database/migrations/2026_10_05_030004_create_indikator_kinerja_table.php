<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator_kinerja', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->string('satuan', 50);
            $table->enum('polaritas', ['positif', 'negatif'])->default('positif');
            $table->enum('level', ['strategis', 'program', 'kegiatan'])->default('kegiatan');
            $table->timestamps();

            $table->index(['level', 'polaritas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator_kinerja');
    }
};
