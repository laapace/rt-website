<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tagihan_id')->constrained('tagihan_iuran')->restrictOnDelete();
            $table->foreignUuid('dibayar_oleh_user_id')->constrained('users')->restrictOnDelete();
            $table->bigInteger('nominal_dibayar');
            $table->string('metode')->default('transfer');
            $table->string('bukti_path')->nullable();
            $table->date('tanggal_bayar');
            $table->enum('status', ['menunggu_verifikasi', 'terverifikasi', 'ditolak'])->default('menunggu_verifikasi');
            $table->text('alasan_penolakan')->nullable();
            $table->foreignUuid('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index('tagihan_id', 'idx_pembayaran_tagihan');
            $table->index('status', 'idx_pembayaran_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
