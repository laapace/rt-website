<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\KartuKeluarga;
use App\Models\Kas;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\TagihanIuran;
use App\Models\TarifIuran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IuranService
{
    /**
     * Generate tagihan untuk satu periode (format 'YYYY-MM') bagi semua
     * KK aktif — SATU tagihan per KK per TARIF aktif. RT 02 RW 03 punya
     * 2 tarif aktif (Iuran Bulanan Rp30.000 + Iuran Sampah Rp10.000),
     * jadi 10 KK menghasilkan 20 tagihan per bulan. Jatuh tempo tanggal 5.
     * Aman dipanggil 2 kali: firstOrCreate + unique index
     * (kartu_keluarga_id, tarif_id, periode) mencegah tagihan ganda.
     * Bila $tarifId diisi, hanya tarif itu yang di-generate.
     *
     * @return array{dibuat: int, sudah_ada: int}
     */
    public function generateTagihan(string $periode, ?string $tarifId = null): array
    {
        $tarifs = $tarifId
            ? collect([TarifIuran::findOrFail($tarifId)])
            : TarifIuran::where('is_active', true)->orderBy('periode_mulai')->get();

        if ($tarifs->isEmpty()) {
            throw new \Exception('Belum ada tarif iuran aktif.');
        }

        [$tahun, $bulan] = explode('-', $periode);
        // Jatuh tempo tanggal 5 setiap bulan (ketentuan RT 02 RW 03).
        $jatuhTempo = sprintf('%04d-%02d-05', (int) $tahun, (int) $bulan);

        $dibuat = 0;
        $sudahAda = 0;

        DB::transaction(function () use ($periode, $tarifs, $jatuhTempo, &$dibuat, &$sudahAda) {
            $daftarKk = KartuKeluarga::where('status_tinggal', 'aktif')->get();

            foreach ($tarifs as $tarif) {
                foreach ($daftarKk as $kk) {
                    $tagihan = TagihanIuran::firstOrCreate(
                        ['kartu_keluarga_id' => $kk->id, 'tarif_id' => $tarif->id, 'periode' => $periode],
                        [
                            'nominal' => $tarif->nominal,
                            'jatuh_tempo' => $jatuhTempo,
                            'status' => 'belum_bayar',
                        ]
                    );

                    if ($tagihan->wasRecentlyCreated) {
                        $dibuat++;

                        $penerima = User::where('kartu_keluarga_id', $kk->id)
                            ->where('is_active', true)
                            ->get();

                        foreach ($penerima as $user) {
                            Notifikasi::create([
                                'user_id' => $user->id,
                                'judul' => 'Tagihan baru: '.$tarif->nama.' '.$periode,
                                'isi' => 'Tagihan '.$tarif->nama.' periode '.$periode.' sebesar '.formatRupiah($tarif->nominal).' sudah diterbitkan. Jatuh tempo '.$jatuhTempo.'.',
                                'tautan' => '/dashboard/iuran',
                            ]);
                        }
                    } else {
                        $sudahAda++;
                    }
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'generate_tagihan',
                'tabel_terkait' => 'tagihan_iuran',
                'record_id' => null,
                'detail' => ['periode' => $periode, 'tarif' => $tarifs->pluck('nama')->all(), 'dibuat' => $dibuat, 'sudah_ada' => $sudahAda],
            ]);
        });

        return ['dibuat' => $dibuat, 'sudah_ada' => $sudahAda];
    }

    /**
     * Verifikasi pembayaran oleh Bendahara: setuju => pembayaran
     * terverifikasi + kas masuk + tagihan lunas; tolak => pembayaran
     * ditolak (alasan wajib) + tagihan kembali belum_bayar.
     * Semua dalam satu transaksi, baris pembayaran dikunci.
     */
    public function verifikasiPembayaran(Pembayaran $pembayaran, bool $disetujui, ?string $alasan): void
    {
        DB::transaction(function () use ($pembayaran, $disetujui, $alasan) {
            $pembayaran = Pembayaran::where('id', $pembayaran->id)->lockForUpdate()->firstOrFail();

            if ($pembayaran->status !== 'menunggu_verifikasi') {
                throw new \Exception('Pembayaran ini sudah diproses.');
            }

            if ($disetujui) {
                // Pertahanan kedua: tagihan yang sudah lunas tidak boleh
                // diverifikasi lagi (mencegah kas ganda bila ada celah lain).
                if ($pembayaran->tagihan->status === 'lunas') {
                    throw new \Exception('Tagihan ini sudah lunas.');
                }

                $pembayaran->update([
                    'status' => 'terverifikasi',
                    'diverifikasi_oleh' => Auth::id(),
                    'diverifikasi_pada' => now(),
                ]);

                Kas::create([
                    'jenis' => 'masuk',
                    'kategori' => 'Iuran',
                    'nominal' => $pembayaran->nominal_dibayar,
                    'keterangan' => 'Iuran '.$pembayaran->tagihan->periode.' — KK '.$pembayaran->tagihan->kartuKeluarga->no_kk,
                    'tanggal' => now()->toDateString(),
                    'pembayaran_id' => $pembayaran->id,
                    'dicatat_oleh' => Auth::id() ?? $pembayaran->dibayar_oleh_user_id,
                ]);

                $pembayaran->tagihan->update(['status' => 'lunas']);

                $judul = 'Pembayaran iuran '.$pembayaran->tagihan->periode.' terverifikasi';
                $isi = 'Pembayaran sebesar '.formatRupiah($pembayaran->nominal_dibayar).' untuk iuran '.$pembayaran->tagihan->periode.' telah diverifikasi Bendahara.';
                $aksi = 'verifikasi_pembayaran';
            } else {
                if (empty(trim((string) $alasan))) {
                    throw new \Exception('Alasan penolakan wajib diisi.');
                }

                $pembayaran->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $alasan,
                    'diverifikasi_oleh' => Auth::id(),
                    'diverifikasi_pada' => now(),
                ]);

                // Tagihan kembali 'belum_bayar' agar warga bisa upload ulang.
                $pembayaran->tagihan->update(['status' => 'belum_bayar']);

                $judul = 'Pembayaran iuran '.$pembayaran->tagihan->periode.' ditolak';
                $isi = 'Pembayaran iuran '.$pembayaran->tagihan->periode.' ditolak: '.$alasan;
                $aksi = 'tolak_pembayaran';
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => $aksi,
                'tabel_terkait' => 'pembayaran',
                'record_id' => $pembayaran->id,
                'detail' => [
                    'tagihan_id' => $pembayaran->tagihan_id,
                    'periode' => $pembayaran->tagihan->periode,
                    'nominal' => $pembayaran->nominal_dibayar,
                    'disetujui' => $disetujui,
                ],
            ]);

            Notifikasi::create([
                'user_id' => $pembayaran->dibayar_oleh_user_id,
                'judul' => $judul,
                'isi' => $isi,
                'tautan' => '/dashboard/iuran',
            ]);
        });
    }

    /**
     * Warga mengunggah bukti bayar untuk sebuah tagihan.
     *
     * $data: nominal_dibayar, metode, tanggal_bayar, dibayar_oleh_user_id.
     * $file: UploadedFile bukti (sudah divalidasi di Form Request).
     */
    public function uploadBukti(TagihanIuran $tagihan, array $data, $file): Pembayaran
    {
        return DB::transaction(function () use ($tagihan, $data, $file) {
            // Tagihan lunas / sedang diverifikasi tidak bisa dibayar ulang —
            // tanpa ini, verifikasi kedua akan menggandakan kas.
            if (! in_array($tagihan->status, ['belum_bayar', 'terlambat'], true)) {
                throw new \Exception('Tagihan ini sudah dibayar atau sedang menunggu verifikasi.');
            }

            $buktiPath = null;

            if ($file instanceof UploadedFile) {
                $buktiPath = $file->store('bukti-bayar', 'public');
            } elseif (is_string($file)) {
                $buktiPath = $file;
            }

            // Nominal dipaksa sesuai tagihan: nilai dari form (hidden field)
            // bisa diubah dari browser, kas harus tercatat sebesar tagihan.
            $pembayaran = Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'dibayar_oleh_user_id' => $data['dibayar_oleh_user_id'] ?? Auth::id(),
                'nominal_dibayar' => $tagihan->nominal,
                'metode' => $data['metode'] ?? 'transfer',
                'bukti_path' => $buktiPath,
                'tanggal_bayar' => $data['tanggal_bayar'],
                'status' => 'menunggu_verifikasi',
            ]);

            $tagihan->update(['status' => 'menunggu_verifikasi']);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'upload_bukti_bayar',
                'tabel_terkait' => 'pembayaran',
                'record_id' => $pembayaran->id,
                'detail' => [
                    'tagihan_id' => $tagihan->id,
                    'periode' => $tagihan->periode,
                    'nominal' => $pembayaran->nominal_dibayar,
                ],
            ]);

            return $pembayaran;
        });
    }
}
