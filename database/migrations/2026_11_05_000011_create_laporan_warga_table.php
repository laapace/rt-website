<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_warga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warga_id')->constrained('warga')->restrictOnDelete();
            $table->foreignUuid('dilaporkan_oleh_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('kategori', ['kebersihan', 'keamanan', 'fasilitas', 'lainnya']);
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('foto_path')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->text('komentar_pengurus')->nullable();
            $table->foreignUuid('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_warga');
    }
};
