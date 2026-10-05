<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_renaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_aksi_id')->unique()->constrained('rencana_aksi')->cascadeOnDelete();
            $table->text('deskripsi_realisasi')->nullable();
            $table->unsignedTinyInteger('persentase_selesai')->default(0);
            $table->enum('status', ['belum_lapor', 'menunggu_verifikasi', 'terverifikasi', 'perlu_perbaikan'])
                ->default('belum_lapor');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'persentase_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_renaksi');
    }
};
