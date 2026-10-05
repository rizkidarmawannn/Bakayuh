<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_area', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['lke_wbk_wbbm', 'rkt_general', 'rkt_tematik', 'rkt_meso']);
            $table->enum('komponen', ['pengungkit', 'hasil', 'none'])->default('none');
            $table->string('kode', 30)->unique();
            $table->string('nama_area');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['kategori', 'komponen', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_area');
    }
};
