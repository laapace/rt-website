<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya updated_at — tidak ada created_at (lihat schema.md §3.18).
        Schema::create('nomor_surat_counter', function (Blueprint $table) {
            $table->integer('tahun')->primary();
            $table->bigInteger('nomor_terakhir')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_counter');
    }
};
