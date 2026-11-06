<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KasService
{
    /**
     * Saldo kas dari view v_kas_saldo (fallback ke query langsung
     * kalau view belum ada, mis. sebelum migrasi view dijalankan).
     *
     * @return array{total_masuk: int, total_keluar: int, saldo: int}
     */
    public function saldo(): array
    {
        try {
            $row = DB::table('v_kas_saldo')->first();

            if ($row) {
                return [
                    'total_masuk' => (int) $row->total_masuk,
                    'total_keluar' => (int) $row->total_keluar,
                    'saldo' => (int) $row->saldo,
                ];
            }
        } catch (\Throwable) {
            // View belum ada — hitung langsung dari tabel kas.
        }

        $totalMasuk = (int) DB::table('kas')->whereNull('deleted_at')->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) DB::table('kas')->whereNull('deleted_at')->where('jenis', 'keluar')->sum('nominal');

        return [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo' => $totalMasuk - $totalKeluar,
        ];
    }

    /**
     * Ringkasan kas satu bulan (periode format 'YYYY-MM')
     * dari view v_kas_bulanan.
     *
     * @return array{periode: string, masuk: int, keluar: int}
     */
    public function ringkasanBulan(string $periode): array
    {
        try {
            $row = DB::table('v_kas_bulanan')->where('periode', $periode)->first();

            if ($row) {
                return [
                    'periode' => $periode,
                    'masuk' => (int) $row->masuk,
                    'keluar' => (int) $row->keluar,
                ];
            }
        } catch (\Throwable) {
            // View belum ada — hitung langsung dari tabel kas.
        }

        $masuk = (int) DB::table('kas')
            ->whereNull('deleted_at')->where('jenis', 'masuk')
            ->whereRaw($this->periodeExpr().' = ?', [$periode])->sum('nominal');
        $keluar = (int) DB::table('kas')
            ->whereNull('deleted_at')->where('jenis', 'keluar')
            ->whereRaw($this->periodeExpr().' = ?', [$periode])->sum('nominal');

        return ['periode' => $periode, 'masuk' => $masuk, 'keluar' => $keluar];
    }

    /**
     * Ringkasan kas per bulan (untuk warga — sama dengan isi
     * view v_kas_ringkas).
     */
    public function ringkasan(): Collection
    {
        try {
            return DB::table('v_kas_ringkas')->orderBy('periode', 'desc')->get();
        } catch (\Throwable) {
            return DB::table('kas')
                ->selectRaw($this->periodeExpr().' as periode')
                ->selectRaw("SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) as masuk")
                ->selectRaw("SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) as keluar")
                ->whereNull('deleted_at')
                ->groupBy('periode')
                ->orderBy('periode', 'desc')
                ->get();
        }
    }

    private function periodeExpr(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', tanggal)"
            : "DATE_FORMAT(tanggal, '%Y-%m')";
    }
}
