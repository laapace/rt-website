<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kartu_keluarga_id')->constrained('kartu_keluarga')->restrictOnDelete();
            $table->string('nik')->unique();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('hubungan_keluarga');
            $table->string('pekerjaan')->nullable();
            $table->enum('status_tinggal', ['aktif', 'pindah', 'meninggal'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('kartu_keluarga_id', 'idx_warga_kk');
            $table->index('nama_lengkap', 'idx_warga_nama');
            $table->index('nik', 'idx_warga_nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga');
    }
};
