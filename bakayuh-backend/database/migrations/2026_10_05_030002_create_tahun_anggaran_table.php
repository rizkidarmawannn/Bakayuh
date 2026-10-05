<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();

            $table->index('is_aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};
