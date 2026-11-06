<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pengajuan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warga_id')->constrained('warga')->restrictOnDelete();
            $table->foreignUuid('diajukan_oleh_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('jenis', ['domisili', 'tidak_mampu', 'usaha', 'kelahiran', 'kematian', 'lainnya']);
            $table->text('keperluan');
            $table->enum('status', ['diajukan', 'diproses', 'disetujui', 'ditolak'])->default('diajukan');
            $table->string('nomor_surat')->nullable()->unique();
            $table->text('alasan_penolakan')->nullable();
            $table->foreignUuid('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->json('data_surat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pengajuan');
    }
};
