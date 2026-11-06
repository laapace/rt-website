<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('kategori');
            $table->bigInteger('nominal');
            $table->text('keterangan');
            $table->date('tanggal');
            $table->foreignUuid('pembayaran_id')->nullable()->unique()->constrained('pembayaran')->nullOnDelete();
            $table->string('bukti_path')->nullable();
            $table->foreignUuid('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
