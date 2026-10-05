<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_sub_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rb_indikator_id')->constrained('rb_indikator')->cascadeOnDelete();
            $table->unsignedSmallInteger('nomor_poin')->default(1);
            $table->string('judul_poin');
            $table->text('checklist_daduk')->nullable();
            $table->text('catatan_tpi')->nullable();
            $table->timestamps();

            $table->index(['rb_indikator_id', 'nomor_poin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_sub_indikator');
    }
};
