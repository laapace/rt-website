<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_keluarga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_kk')->unique();
            $table->text('alamat');
            $table->string('blok');
            $table->string('rt')->default('05');
            $table->string('rw')->default('03');
            // Kolom saja di sini; FK ke warga.id dibuat di migrasi
            // 2026_11_05_000018 karena tabel warga belum ada (FK melingkar).
            $table->uuid('kepala_keluarga_id')->nullable();
            $table->enum('status_tinggal', ['aktif', 'pindah', 'meninggal'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('blok', 'idx_kk_blok');
            $table->index('status_tinggal', 'idx_kk_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_keluarga');
    }
};
