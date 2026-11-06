<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\NomorSuratCounter;
use App\Models\Notifikasi;
use App\Models\PengaturanRt;
use App\Models\SuratPengajuan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratService
{
    /**
     * Menyetujui surat: ambil nomor urut tahun berjalan dari
     * nomor_surat_counter dengan lockForUpdate (cegah nomor ganda),
     * lalu update surat + log + notifikasi dalam satu transaksi.
     *
     * Format nomor: 001/RT.02/RW.03/X/2026 (RT/RW dibaca dari pengaturan_rt).
     *
     * @return string nomor surat yang diterbitkan
     */
    public function setujuiSurat(SuratPengajuan $surat): string
    {
        return DB::transaction(function () use ($surat) {
            $surat = SuratPengajuan::where('id', $surat->id)->lockForUpdate()->firstOrFail();

            if (in_array($surat->status, ['disetujui', 'ditolak'], true)) {
                throw new \Exception('Surat ini sudah diproses sebelumnya.');
            }

            $tahun = (int) now()->format('Y');

            $counter = NomorSuratCounter::where('tahun', $tahun)->lockForUpdate()->first()
                ?? NomorSuratCounter::create(['tahun' => $tahun, 'nomor_terakhir' => 0, 'updated_at' => now()]);

            $counter->increment('nomor_terakhir');
            $counter->update(['updated_at' => now()]);

            $pengaturan = PengaturanRt::first();
            $rt = $this->angkaDariNama($pengaturan?->nama_rt, '02');
            $rw = $this->angkaDariNama($pengaturan?->nama_rw, '03');

            $nomor = sprintf('%03d/RT.%s/RW.%s/%s/%d',
                $counter->nomor_terakhir,
                $rt,
                $rw,
                bulanRomawi((int) now()->format('m')),
                $tahun
            );

            $surat->update([
                'status' => 'disetujui',
                'nomor_surat' => $nomor,
                'diproses_oleh' => Auth::id(),
                'diproses_pada' => now(),
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'setujui_surat',
                'tabel_terkait' => 'surat_pengajuan',
                'record_id' => $surat->id,
                'detail' => ['nomor_surat' => $nomor, 'jenis' => $surat->jenis],
            ]);

            Notifikasi::create([
                'user_id' => $surat->diajukan_oleh_user_id,
                'judul' => 'Surat pengajuan disetujui',
                'isi' => 'Pengajuan surat '.$surat->jenis.' Anda disetujui dengan nomor '.$nomor.'.',
                'tautan' => '/dashboard/surat',
            ]);

            return $nomor;
        });
    }

    /**
     * Menolak surat (alasan wajib).
     */
    public function tolakSurat(SuratPengajuan $surat, string $alasan): void
    {
        if (empty(trim($alasan))) {
            throw new \Exception('Alasan penolakan wajib diisi.');
        }

        DB::transaction(function () use ($surat, $alasan) {
            $surat = SuratPengajuan::where('id', $surat->id)->lockForUpdate()->firstOrFail();

            if (in_array($surat->status, ['disetujui', 'ditolak'], true)) {
                throw new \Exception('Surat ini sudah diproses sebelumnya.');
            }

            $surat->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $alasan,
                'diproses_oleh' => Auth::id(),
                'diproses_pada' => now(),
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'tolak_surat',
                'tabel_terkait' => 'surat_pengajuan',
                'record_id' => $surat->id,
                'detail' => ['jenis' => $surat->jenis, 'alasan' => $alasan],
            ]);

            Notifikasi::create([
                'user_id' => $surat->diajukan_oleh_user_id,
                'judul' => 'Surat pengajuan ditolak',
                'isi' => 'Pengajuan surat '.$surat->jenis.' Anda ditolak: '.$alasan,
                'tautan' => '/dashboard/surat',
            ]);
        });
    }

    /**
     * Ambil angka dari nama seperti 'RT 02' / '02' => '02'.
     */
    private function angkaDariNama(?string $nama, string $default): string
    {
        if ($nama && preg_match('/(\d+)/', $nama, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        return $default;
    }
}
