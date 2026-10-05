<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->enum('tipe', ['kanwil', 'upt', 'satker'])->default('upt');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tipe', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan_kerja');
    }
};
