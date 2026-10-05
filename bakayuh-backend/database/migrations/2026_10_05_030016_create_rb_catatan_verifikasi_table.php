<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rb_catatan_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rb_target_periode_id')->constrained('rb_target_periode')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('pesan');
            $table->timestamp('created_at')->useCurrent();

            $table->index('rb_target_periode_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rb_catatan_verifikasi');
    }
};
