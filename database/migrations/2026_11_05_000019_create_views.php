<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * View laporan (schema.md §4). MySQL memakai DATE_FORMAT(),
     * SQLite tidak punya fungsi itu — di SQLite dipakai
     * strftime('%Y-%m', tanggal). Dipilih berdasarkan driver aktif
     * supaya migrasi jalan di MySQL (produksi) maupun SQLite (uji lokal).
     * SQLite juga tidak mendukung CREATE OR REPLACE VIEW, jadi view
     * selalu di-DROP dulu sebelum dibuat.
     */
    public function up(): void
    {
        $periodeExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', tanggal)"
            : "DATE_FORMAT(tanggal, '%Y-%m')";

        DB::statement('DROP VIEW IF EXISTS v_kas_saldo');
        DB::statement("
            CREATE VIEW v_kas_saldo AS
            SELECT
              COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
              COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END), 0) AS total_keluar,
              COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE -nominal END), 0) AS saldo
            FROM kas WHERE deleted_at IS NULL
        ");

        DB::statement('DROP VIEW IF EXISTS v_kas_bulanan');
        DB::statement("
            CREATE VIEW v_kas_bulanan AS
            SELECT
              {$periodeExpr} AS periode,
              SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) AS masuk,
              SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) AS keluar
            FROM kas WHERE deleted_at IS NULL
            GROUP BY periode ORDER BY periode DESC
        ");

        DB::statement('DROP VIEW IF EXISTS v_iuran_periode');
        DB::statement("
            CREATE VIEW v_iuran_periode AS
            SELECT
              periode,
              COUNT(*) AS total_tagihan,
              SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) AS lunas,
              SUM(CASE WHEN status = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS menunggu,
              SUM(CASE WHEN status IN ('belum_bayar', 'terlambat') THEN 1 ELSE 0 END) AS belum_bayar,
              SUM(CASE WHEN status = 'lunas' THEN nominal ELSE 0 END) AS terkumpul
            FROM tagihan_iuran GROUP BY periode ORDER BY periode DESC
        ");

        DB::statement('DROP VIEW IF EXISTS v_kas_ringkas');
        DB::statement("
            CREATE VIEW v_kas_ringkas AS
            SELECT
              {$periodeExpr} AS periode,
              SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) AS masuk,
              SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) AS keluar
            FROM kas WHERE deleted_at IS NULL GROUP BY periode
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_kas_ringkas');
        DB::statement('DROP VIEW IF EXISTS v_iuran_periode');
        DB::statement('DROP VIEW IF EXISTS v_kas_bulanan');
        DB::statement('DROP VIEW IF EXISTS v_kas_saldo');
    }
};
