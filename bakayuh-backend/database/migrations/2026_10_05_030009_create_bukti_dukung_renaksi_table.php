<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_dukung_renaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_renaksi_id')->constrained('realisasi_renaksi')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('path_file');
            $table->unsignedInteger('ukuran_file'); // bytes, max 10MB
            $table->string('mime_type', 100);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('realisasi_renaksi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_dukung_renaksi');
    }
};
