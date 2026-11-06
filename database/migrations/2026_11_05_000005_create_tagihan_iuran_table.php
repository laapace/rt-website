<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_iuran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kartu_keluarga_id')->constrained('kartu_keluarga')->restrictOnDelete();
            $table->foreignUuid('tarif_id')->constrained('tarif_iuran')->restrictOnDelete();
            $table->string('periode');
            $table->bigInteger('nominal');
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'terlambat'])->default('belum_bayar');
            $table->timestamps();

            // Satu tagihan per KK per TARIF per periode — RT 02 punya 2 tarif
            // aktif (Iuran Bulanan + Iuran Sampah), jadi satu KK punya 2 tagihan/bulan.
            $table->unique(['kartu_keluarga_id', 'tarif_id', 'periode']);
            $table->index('periode', 'idx_tagihan_periode');
            $table->index('status', 'idx_tagihan_status');
            $table->index('kartu_keluarga_id', 'idx_tagihan_kk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_iuran');
    }
};
