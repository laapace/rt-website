<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ronda_petugas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jadwal_ronda_id')->constrained('jadwal_ronda')->cascadeOnDelete();
            $table->foreignUuid('warga_id')->constrained('warga')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['jadwal_ronda_id', 'warga_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ronda_petugas');
    }
};
