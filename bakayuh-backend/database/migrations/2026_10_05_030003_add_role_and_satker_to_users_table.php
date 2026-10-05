<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin_kanwil', 'operator_satker', 'viewer'])
                ->default('operator_satker')
                ->after('email');
            $table->foreignId('satker_id')
                ->nullable()
                ->after('role')
                ->constrained('satuan_kerja')
                ->nullOnDelete();
            $table->boolean('is_active')
                ->default(true)
                ->after('satker_id');

            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn(['role', 'satker_id', 'is_active']);
        });
    }
};
