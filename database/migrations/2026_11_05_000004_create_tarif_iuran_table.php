<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_iuran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->bigInteger('nominal');
            $table->date('periode_mulai');
            $table->date('periode_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_iuran');
    }
};
