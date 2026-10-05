<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rb_area_id')->constrained('rb_area')->cascadeOnDelete();
            $table->enum('aspek', ['reform', 'pemenuhan', 'hasil', 'none'])->default('none');
            $table->string('kode', 30);
            $table->string('nama_indikator');
            $table->text('keterangan_juknis')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['rb_area_id', 'aspek', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_indikator');
    }
};
