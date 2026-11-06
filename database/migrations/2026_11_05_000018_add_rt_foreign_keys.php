<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FK yang tidak bisa dibuat di migrasi pembuat tabel karena
     * tabel rujukannya belum ada saat itu (FK melingkar / urutan):
     * - kartu_keluarga.kepala_keluarga_id -> warga.id
     * - users.kartu_keluarga_id -> kartu_keluarga.id
     * - users.warga_id -> warga.id
     */
    public function up(): void
    {
        Schema::table('kartu_keluarga', function (Blueprint $table) {
            $table->foreign('kepala_keluarga_id')->references('id')->on('warga')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kartu_keluarga_id')->references('id')->on('kartu_keluarga')->nullOnDelete();
            $table->foreign('warga_id')->references('id')->on('warga')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kartu_keluarga_id']);
            $table->dropForeign(['warga_id']);
        });

        Schema::table('kartu_keluarga', function (Blueprint $table) {
            $table->dropForeign(['kepala_keluarga_id']);
        });
    }
};
