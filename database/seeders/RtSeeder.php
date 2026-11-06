<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\Kas;
use App\Models\LaporanWarga;
use App\Models\NomorSuratCounter;
use App\Models\Pembayaran;
use App\Models\PengaturanRt;
use App\Models\Pengumuman;
use App\Models\RondaPetugas;
use App\Models\SuratPengajuan;
use App\Models\TagihanIuran;
use App\Models\TarifIuran;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DATA RT ASLI (dikirim pengguna 5 Nov 2026, 14:37 WIB).
 *
 * RT 02 RW 03, Kelurahan Bendan Kergon, Kecamatan Pekalongan Barat,
 * Kota Pekalongan, alamat Bendan Kergon Gg.1 No. 45, telepon 0812-3345-6677.
 * Pengurus: Ketua Abdullah, Sekretaris Rina, Bendahara Ayu (hanya nama depan,
 * sesuai yang dikirim pengguna — jangan tambahkan nama belakang karangan).
 * Tarif: Iuran Bulanan Rp30.000/bln/KK + Iuran Sampah Rp10.000/bln/KK,
 * keduanya aktif, jatuh tempo tanggal 5. 5 jenis surat default.
 * ASUMSI: Kota Pekalongan diasumsikan dari Kecamatan Pekalongan Barat
 * (pengguna tidak menyebut kota secara eksplisit).
 *
 * DATA WARGA TETAP FIKTIF (nama, NIK/KK diawali 3273, alamat gang) —
 * bukan data warga asli; pengurus menginput data asli lewat halaman Admin.
 * Akun demo password 'password123'.
 */
class RtSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: kalau database sudah berisi user (mis. deploy ulang di
        // Railway menjalankan seed lagi), lewati — jangan buat data ganda.
        // migrate:fresh --seed tetap mengisi karena tabelnya kosong.
        if (User::count() > 0) {
            return;
        }

        // -----------------------------------------------------------------
        // 1. Pengaturan RT (1 baris)
        // -----------------------------------------------------------------
        PengaturanRt::create([
            'nama_rt' => 'RT 02',
            'nama_rw' => 'RW 03',
            'kelurahan' => 'Bendan Kergon',
            'kecamatan' => 'Pekalongan Barat',
            // Kota Pekalongan = asumsi dari Kecamatan Pekalongan Barat.
            'kota' => 'Pekalongan',
            'alamat' => 'Bendan Kergon Gg.1 No. 45',
            'telepon' => '0812-3345-6677',
            'nama_ketua' => 'Abdullah',
        ]);

        // -----------------------------------------------------------------
        // 2. Dua tarif aktif sejak 2026-01-01, jatuh tempo tanggal 5:
        //    Iuran Bulanan Rp30.000/bln/KK + Iuran Sampah Rp10.000/bln/KK.
        //    Generate tagihan membuat 1 tagihan per KK per tarif per periode.
        // -----------------------------------------------------------------
        $tarifBulanan = TarifIuran::create([
            'nama' => 'Iuran Bulanan',
            'nominal' => 30000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => null,
            'is_active' => true,
        ]);

        $tarifSampah = TarifIuran::create([
            'nama' => 'Iuran Sampah',
            'nominal' => 10000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => null,
            'is_active' => true,
        ]);

        $daftarTarif = [$tarifBulanan, $tarifSampah];

        // -----------------------------------------------------------------
        // 3. 10 KK (±32 warga) — urutan buat KK + kepala keluarga
        //    mengikuti schema.md §7 (KK dulu, lalu warga, lalu update
        //    kepala_keluarga_id) karena FK-nya melingkar.
        // -----------------------------------------------------------------
        $keluarga = [
            // [blok, [ [nama, jenis_kelamin, hubungan, pekerjaan], ... ]]
            ['Blok A', [
                ['Abdullah', 'L', 'Kepala Keluarga', 'Wiraswasta'],
                ['Rina', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Andi Pratama', 'L', 'Anak', 'Pelajar'],
                ['Dewi Anggraini', 'P', 'Anak', 'Pelajar'],
            ]],
            ['Blok A', [
                ['Ayu', 'P', 'Kepala Keluarga', 'Karyawan Swasta'],
                ['Sari Marlina', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Rizky Ramadhan', 'L', 'Anak', 'Mahasiswa'],
            ]],
            ['Blok A', [
                ['Bambang Riyanto', 'L', 'Kepala Keluarga', 'Pedagang'],
                ['Ratna Sari', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Fajar Nugroho', 'L', 'Anak', 'Pelajar'],
                ['Laras Lestari', 'P', 'Anak', 'Pelajar'],
            ]],
            ['Blok A', [
                ['Joko Susilo', 'L', 'Kepala Keluarga', 'Karyawan Swasta'],
                ['Maya Putri', 'P', 'Istri', 'Guru'],
                ['Dimas Saputra', 'L', 'Anak', 'Pelajar'],
            ]],
            ['Blok A', [
                ['Slamet Raharjo', 'L', 'Kepala Keluarga', 'Buruh'],
                ['Nur Hidayah', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Sari Wulandari', 'P', 'Anak', 'Mahasiswa'],
            ]],
            ['Blok B', [
                ['Eko Saputra', 'L', 'Kepala Keluarga', 'Wiraswasta'],
                ['Lilis Suryani', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Budi Hartono', 'L', 'Anak', 'Pelajar'],
            ]],
            ['Blok B', [
                ['Dedi Kurniawan', 'L', 'Kepala Keluarga', 'Karyawan Swasta'],
                ['Fitri Handayani', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Rudi Santoso', 'L', 'Anak', 'Pelajar'],
                ['Intan Permata', 'P', 'Anak', 'Pelajar'],
            ]],
            ['Blok B', [
                ['Rizky Pratama', 'L', 'Kepala Keluarga', 'Pedagang'],
                ['Sari Dewi', 'P', 'Istri', 'Ibu Rumah Tangga'],
                ['Agus Wijaya', 'L', 'Anak', 'Mahasiswa'],
            ]],
            ['Blok B', [
                ['Andi Wijaya', 'L', 'Kepala Keluarga', 'Karyawan Swasta'],
                ['Dewi Lestari', 'P', 'Istri', 'Guru'],
                ['Putri Maharani', 'P', 'Anak', 'Pelajar'],
            ]],
            ['Blok B', [
                ['Fajar Ramadhan', 'L', 'Kepala Keluarga', 'Wiraswasta'],
                ['Nurul Huda', 'P', 'Istri', 'Ibu Rumah Tangga'],
            ]],
        ];

        $nikSeq = 0;
        /** @var KartuKeluarga[] $daftarKk */
        $daftarKk = [];
        /** @var Warga[][] $anggotaPerKk */
        $anggotaPerKk = [];

        foreach ($keluarga as $i => [$blok, $anggota]) {
            $noKk = '327301010101'.sprintf('%04d', $i + 1);

            $kk = KartuKeluarga::create([
                'no_kk' => $noKk,
                'alamat' => 'Bendan Kergon Gg.1 No. '.(45 + $i).', '.$blok,
                'blok' => $blok,
                'rt' => '02',
                'rw' => '03',
                'kepala_keluarga_id' => null,
                'status_tinggal' => 'aktif',
            ]);

            $daftarWarga = [];
            foreach ($anggota as [$nama, $jk, $hubungan, $pekerjaan]) {
                $nikSeq++;
                $warga = Warga::create([
                    'kartu_keluarga_id' => $kk->id,
                    'nik' => '3273010101'.sprintf('%06d', $nikSeq),
                    'nama_lengkap' => $nama,
                    'tempat_lahir' => 'Pekalongan',
                    'tanggal_lahir' => '1985-01-01',
                    'jenis_kelamin' => $jk,
                    'hubungan_keluarga' => $hubungan,
                    'pekerjaan' => $pekerjaan,
                    'status_tinggal' => 'aktif',
                ]);
                $daftarWarga[] = $warga;
            }

            // Kepala keluarga = anggota pertama.
            $kk->update(['kepala_keluarga_id' => $daftarWarga[0]->id]);

            $daftarKk[] = $kk;
            $anggotaPerKk[] = $daftarWarga;
        }

        // -----------------------------------------------------------------
        // 4. Users + roles (semua is_active true, password 'password123')
        // -----------------------------------------------------------------
        $buatUser = function (string $nama, string $email, string $peran, ?KartuKeluarga $kk = null, ?Warga $warga = null): User {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make('password123'),
                'no_hp' => '081234567890',
                'kartu_keluarga_id' => $kk?->id,
                'warga_id' => $warga?->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            UserRole::create(['user_id' => $user->id, 'peran' => $peran]);

            return $user;
        };

        $ketua = $buatUser('Abdullah', 'ketua@rt02.example', 'ketua', $daftarKk[0], $anggotaPerKk[0][0]);
        $sekretaris = $buatUser('Rina', 'sekretaris@rt02.example', 'sekretaris', $daftarKk[0], $anggotaPerKk[0][1]);
        $bendahara = $buatUser('Ayu', 'bendahara@rt02.example', 'bendahara', $daftarKk[1], $anggotaPerKk[1][0]);
        $admin = $buatUser('Admin Sistem', 'admin@rt02.example', 'admin');
        // Akun warga contoh: kepala KK pertama (akun kedua Abdullah,
        // khusus peran warga — sesuai penugasan).
        $warga1 = $buatUser('Abdullah', 'warga1@rt02.example', 'warga', $daftarKk[0], $anggotaPerKk[0][0]);
        // No. HP Ketua terpisah tidak ada (tanda "-") — pakai telepon RT.
        $ketua->update(['no_hp' => '0812-3345-6677']);

        // Pembayar fallback: user pertama yang terhubung ke KK itu,
        // kalau tidak ada pakai akun ketua.
        $pembayarUntuk = function (KartuKeluarga $kk) use ($ketua): User {
            return User::where('kartu_keluarga_id', $kk->id)->first() ?? $ketua;
        };

        // -----------------------------------------------------------------
        // 5. Tagihan 2026-10 & 2026-11 untuk semua KK (status campuran)
        // -----------------------------------------------------------------
        // Indeks KK (1-based) per status, per periode.
        $rencana = [
            '2026-10' => [
                'lunas' => [1, 2, 3, 4, 5, 9],
                'menunggu_verifikasi' => [6, 7],
                'belum_bayar' => [8, 10],
            ],
            '2026-11' => [
                'lunas' => [1, 2, 3, 4, 8],
                'menunggu_verifikasi' => [5, 9],
                // KK 10: pembayaran ditolak -> tagihan kembali belum_bayar.
                'belum_bayar' => [6, 7, 10],
            ],
        ];

        // Status yang sama berlaku untuk KEDUA tarif satu KK: tiap KK punya
        // 2 tagihan per periode (Bulanan Rp30.000 + Sampah Rp10.000).
        // Per periode: 20 tagihan (10 KK x 2 tarif).
        foreach ($rencana as $periode => $statuses) {
            [$tahun, $bulan] = explode('-', $periode);
            // Jatuh tempo tanggal 5 (ketentuan RT 02 RW 03).
            $jatuhTempo = sprintf('%04d-%02d-05', (int) $tahun, (int) $bulan);

            foreach ($statuses as $status => $indeksKk) {
                foreach ($indeksKk as $idx) {
                    $kk = $daftarKk[$idx - 1];
                    $pembayar = $pembayarUntuk($kk);

                    foreach ($daftarTarif as $tarif) {
                        $tagihan = TagihanIuran::create([
                            'kartu_keluarga_id' => $kk->id,
                            'tarif_id' => $tarif->id,
                            'periode' => $periode,
                            'nominal' => $tarif->nominal,
                            'jatuh_tempo' => $jatuhTempo,
                            'status' => $status,
                        ]);

                        if ($status === 'lunas') {
                            $pembayaran = Pembayaran::create([
                                'tagihan_id' => $tagihan->id,
                                'dibayar_oleh_user_id' => $pembayar->id,
                                'nominal_dibayar' => $tarif->nominal,
                                'metode' => 'transfer',
                                'bukti_path' => null,
                                'tanggal_bayar' => $periode.'-04',
                                'status' => 'terverifikasi',
                                'diverifikasi_oleh' => $bendahara->id,
                                'diverifikasi_pada' => $periode.'-05 10:00:00',
                            ]);

                            Kas::create([
                                'jenis' => 'masuk',
                                'kategori' => 'Iuran',
                                'nominal' => $tarif->nominal,
                                'keterangan' => $tarif->nama.' '.$periode.' — KK '.$kk->no_kk,
                                'tanggal' => $periode.'-05',
                                'pembayaran_id' => $pembayaran->id,
                                'dicatat_oleh' => $bendahara->id,
                            ]);
                        } elseif ($status === 'menunggu_verifikasi') {
                            Pembayaran::create([
                                'tagihan_id' => $tagihan->id,
                                'dibayar_oleh_user_id' => $pembayar->id,
                                'nominal_dibayar' => $tarif->nominal,
                                'metode' => 'transfer',
                                'bukti_path' => null,
                                'tanggal_bayar' => $periode.'-04',
                                'status' => 'menunggu_verifikasi',
                            ]);
                        }
                    }
                }
            }
        }

        // 1 pembayaran ditolak (KK 10, periode 2026-11, tarif Iuran Bulanan).
        $tagihanKk10Nov = TagihanIuran::where('kartu_keluarga_id', $daftarKk[9]->id)
            ->where('tarif_id', $tarifBulanan->id)
            ->where('periode', '2026-11')->firstOrFail();
        Pembayaran::create([
            'tagihan_id' => $tagihanKk10Nov->id,
            'dibayar_oleh_user_id' => $pembayarUntuk($daftarKk[9])->id,
            'nominal_dibayar' => $tarifBulanan->nominal,
            'metode' => 'transfer',
            'bukti_path' => null,
            'tanggal_bayar' => '2026-11-04',
            'status' => 'ditolak',
            'alasan_penolakan' => 'Bukti tidak jelas, mohon upload ulang.',
            'diverifikasi_oleh' => $bendahara->id,
            'diverifikasi_pada' => '2026-11-05 10:00:00',
        ]);

        // -----------------------------------------------------------------
        // 6. Kas keluar manual (3 baris)
        // -----------------------------------------------------------------
        foreach ([
            ['Kebersihan', 50000, 'Iuran kebersihan lingkungan', '2026-10-05'],
            ['Kegiatan', 100000, 'Biaya kegiatan kerja bakti', '2026-10-20'],
            ['Perbaikan', 30000, 'Perbaikan lampu jalan Blok B', '2026-11-08'],
        ] as [$kategori, $nominal, $keterangan, $tanggal]) {
            Kas::create([
                'jenis' => 'keluar',
                'kategori' => $kategori,
                'nominal' => $nominal,
                'keterangan' => $keterangan,
                'tanggal' => $tanggal,
                'pembayaran_id' => null,
                'dicatat_oleh' => $bendahara->id,
            ]);
        }

        // -----------------------------------------------------------------
        // 7. Pengumuman (3, 1 pinned) & agenda (2)
        // -----------------------------------------------------------------
        Pengumuman::create([
            'judul' => 'Kerja Bakti Minggu Pagi',
            'isi' => 'Kerja bakti bersama membersihkan lingkungan RT akan dilaksanakan hari Minggu pukul 07.00 di Balai RT. Diharapkan kehadiran seluruh warga.',
            'kategori' => 'kegiatan',
            'is_pinned' => true,
            'is_published' => true,
            'dibuat_oleh' => $ketua->id,
        ]);
        Pengumuman::create([
            'judul' => 'Iuran November 2026',
            'isi' => 'Iuran bulan November 2026: Iuran Bulanan Rp30.000 dan Iuran Sampah Rp10.000 per KK, dapat dibayarkan paling lambat tanggal 5 November 2026.',
            'kategori' => 'keuangan',
            'is_pinned' => false,
            'is_published' => true,
            'dibuat_oleh' => $bendahara->id,
        ]);
        Pengumuman::create([
            'judul' => 'Jadwal Ronda November',
            'isi' => 'Jadwal ronda bulan November sudah diterbitkan. Setiap blok bertugas bergantian setiap hari Jumat malam.',
            'kategori' => 'keamanan',
            'is_pinned' => false,
            'is_published' => true,
            'dibuat_oleh' => $sekretaris->id,
        ]);

        Agenda::create([
            'judul' => 'Kerja Bakti',
            'deskripsi' => 'Membersihkan selokan dan halaman Balai RT.',
            'tanggal_mulai' => '2026-11-08 07:00:00',
            'tanggal_selesai' => '2026-11-08 10:00:00',
            'lokasi' => 'Balai RT',
            'dibuat_oleh' => $ketua->id,
        ]);
        Agenda::create([
            'judul' => 'Rapat Warga',
            'deskripsi' => 'Rapat bulanan warga membahas iuran dan kegiatan RT.',
            'tanggal_mulai' => '2026-11-15 19:00:00',
            'tanggal_selesai' => '2026-11-15 21:00:00',
            'lokasi' => 'Balai RT',
            'dibuat_oleh' => $sekretaris->id,
        ]);

        // -----------------------------------------------------------------
        // 8. Surat pengajuan (2): 1 disetujui + counter, 1 diajukan
        // -----------------------------------------------------------------
        SuratPengajuan::create([
            'warga_id' => $anggotaPerKk[0][0]->id,
            'diajukan_oleh_user_id' => $warga1->id,
            'jenis' => 'domisili',
            'keperluan' => 'Untuk pengurusan KTP baru',
            'status' => 'disetujui',
            'nomor_surat' => '001/RT.02/RW.03/X/2026',
            'diproses_oleh' => $sekretaris->id,
            'diproses_pada' => '2026-10-15 09:00:00',
            'data_surat' => ['alamat' => 'Bendan Kergon Gg.1 No. 45, Blok A'],
        ]);

        NomorSuratCounter::create([
            'tahun' => 2026,
            'nomor_terakhir' => 1,
            'updated_at' => now(),
        ]);

        SuratPengajuan::create([
            'warga_id' => $anggotaPerKk[1][0]->id,
            'diajukan_oleh_user_id' => $bendahara->id,
            'jenis' => 'tidak_mampu',
            'keperluan' => 'Untuk pengurusan bantuan sosial',
            'status' => 'diajukan',
            'data_surat' => null,
        ]);

        // -----------------------------------------------------------------
        // 9. Laporan warga (3): baru / diproses / selesai
        // -----------------------------------------------------------------
        LaporanWarga::create([
            'warga_id' => $anggotaPerKk[2][0]->id,
            'dilaporkan_oleh_user_id' => $ketua->id,
            'kategori' => 'kebersihan',
            'judul' => 'Sampah menumpuk di ujung Gang Melati',
            'deskripsi' => 'Sampah di ujung gang sudah menumpuk selama tiga hari dan menimbulkan bau.',
            'status' => 'baru',
        ]);
        LaporanWarga::create([
            'warga_id' => $anggotaPerKk[5][0]->id,
            'dilaporkan_oleh_user_id' => $ketua->id,
            'kategori' => 'keamanan',
            'judul' => 'Lampu jalan Blok B mati',
            'deskripsi' => 'Lampu jalan di Blok B mati sejak semalam, jalan menjadi gelap.',
            'status' => 'diproses',
            'komentar_pengurus' => 'Sudah dilaporkan ke petugas, menunggu perbaikan.',
            'ditangani_oleh' => $sekretaris->id,
        ]);
        LaporanWarga::create([
            'warga_id' => $anggotaPerKk[7][0]->id,
            'dilaporkan_oleh_user_id' => $ketua->id,
            'kategori' => 'fasilitas',
            'judul' => 'Kursi Balai RT rusak',
            'deskripsi' => 'Dua kursi di Balai RT rusak dan perlu diperbaiki.',
            'status' => 'selesai',
            'komentar_pengurus' => 'Kursi sudah diperbaiki.',
            'ditangani_oleh' => $ketua->id,
        ]);

        // -----------------------------------------------------------------
        // 10. Jadwal ronda 4 Jumat ke depan, Blok A/B bergantian,
        //     2 petugas per jadwal
        // -----------------------------------------------------------------
        $ronda = [
            ['2026-11-06', 'Blok A'],
            ['2026-11-13', 'Blok B'],
            ['2026-11-20', 'Blok A'],
            ['2026-11-27', 'Blok B'],
        ];

        // Petugas diambil dari anggota KK yang bloknya sesuai.
        $petugasPerJadwal = [
            [$anggotaPerKk[0][0], $anggotaPerKk[0][1]],
            [$anggotaPerKk[5][0], $anggotaPerKk[5][1]],
            [$anggotaPerKk[2][0], $anggotaPerKk[2][1]],
            [$anggotaPerKk[6][0], $anggotaPerKk[6][1]],
        ];

        foreach ($ronda as $i => [$tanggal, $blok]) {
            $jadwal = JadwalRonda::create([
                'tanggal' => $tanggal,
                'blok' => $blok,
                'keterangan' => 'Ronda malam '.$blok,
                'dibuat_oleh' => $sekretaris->id,
            ]);

            // attach() tidak bisa dipakai: id pivot adalah UUID tanpa
            // default, dan attach() tidak mengisi UUID — buat baris
            // pivot lewat model RondaPetugas (HasUuids) satu per satu.
            foreach ($petugasPerJadwal[$i] as $warga) {
                RondaPetugas::create([
                    'jadwal_ronda_id' => $jadwal->id,
                    'warga_id' => $warga->id,
                ]);
            }
        }
    }
}
