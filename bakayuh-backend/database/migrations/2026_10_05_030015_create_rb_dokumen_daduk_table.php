<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_dokumen_daduk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rb_target_periode_id')->constrained('rb_target_periode')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('path_file');
            $table->unsignedBigInteger('ukuran_file'); // bytes, max 50MB
            $table->string('mime_type', 100);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('rb_target_periode_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_dokumen_daduk');
    }
};
