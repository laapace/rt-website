<?php

namespace Tests\Feature;

use App\Models\JadwalRonda;
use App\Models\Kas;
use App\Models\LaporanWarga;
use App\Models\Pembayaran;
use App\Models\PengaturanRt;
use App\Models\SuratPengajuan;
use App\Models\TagihanIuran;
use App\Models\User;
use App\Models\Warga;
use App\Services\IuranService;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtBugfix2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RtSeeder::class);
        Storage::fake('public');
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function bayarDanVerifikasi(User $warga, string $periode): TagihanIuran
    {
        app(IuranService::class)->generateTagihan($periode);
        $tagihan = TagihanIuran::untukUser($warga)->where('periode', $periode)->firstOrFail();

        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg', 800, 600)->size(100),
            'nominal_dibayar' => $tagihan->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertRedirect();

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->actingAs($this->user('bendahara@rt02.example'))
            ->post(route('admin.iuran.verifikasi', $pembayaran))->assertRedirect();

        return $tagihan->fresh();
    }

    // --- RONDA -------------------------------------------------------------
    // Hapus jadwal ronda oleh ketua (dulu selalu 403: JadwalRondaPolicy tidak ada).
    public function test_hapus_jadwal_ronda_oleh_ketua_berhasil(): void
    {
        $jadwal = JadwalRonda::firstOrFail();

        $this->actingAs($this->user('ketua@rt02.example'))
            ->delete(route('admin.ronda.destroy', $jadwal))
            ->assertRedirect();

        $this->assertDatabaseMissing('jadwal_ronda', ['id' => $jadwal->id]);
    }

    // Halaman jadwal ronda warga menampilkan jadwal yang ada (dulu selalu kosong:
    // view memakai $jadwal, controller mengirim $jadwalMendatang/$jadwalSaya).
    public function test_halaman_jadwal_ronda_warga_menampilkan_jadwal(): void
    {
        $this->actingAs($this->user('warga1@rt02.example'))
            ->get('/dashboard/jadwal-ronda')->assertOk()
            ->assertSee('Blok A')
            ->assertSee('Giliran Anda')
            ->assertDontSee('Belum ada jadwal ronda');
    }

    // Buat jadwal ronda dengan petugas (dulu form mengirim teks nama, validasi
    // minta array UUID — form tidak pernah bisa berhasil).
    public function test_buat_jadwal_ronda_dengan_petugas_berhasil(): void
    {
        $petugas = Warga::take(2)->pluck('id')->all();

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.ronda.store'), [
                'tanggal' => '2026-12-04',
                'blok' => 'Blok A',
                'keterangan' => 'Ronda malam Blok A',
                'petugas' => $petugas,
            ])->assertRedirect();

        $jadwal = JadwalRonda::whereDate('tanggal', '2026-12-04')->firstOrFail();
        $this->assertEquals('Blok A', $jadwal->blok);
        $this->assertEquals('Ronda malam Blok A', $jadwal->keterangan);
        $this->assertCount(2, $jadwal->petugas);
    }

    // --- LAPORAN -----------------------------------------------------------
    // Komentar pengurus + foto laporan tampil di kedua daftar (dulu view memakai
    // $l->foto/$l->komentar, padahal kolomnya foto_path/komentar_pengurus;
    // form admin pun mengirim field 'komentar' yang tidak dibaca controller).
    public function test_komentar_dan_foto_laporan_tampil(): void
    {
        $warga = $this->user('warga1@rt02.example');

        $this->actingAs($warga)->post(route('dashboard.laporan.store'), [
            'kategori' => 'kebersihan',
            'judul' => 'Tes laporan foto',
            'deskripsi' => 'Ada sampah menumpuk.',
            'foto' => UploadedFile::fake()->image('sampah.jpg', 800, 600)->size(100),
        ])->assertRedirect();

        $laporan = LaporanWarga::where('judul', 'Tes laporan foto')->firstOrFail();
        $this->assertNotNull($laporan->foto_path);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->patch(route('admin.laporan.status', $laporan), [
                'status' => 'diproses',
                'komentar_pengurus' => 'Sedang ditangani petugas.',
            ])->assertRedirect();
        $this->assertEquals('Sedang ditangani petugas.', $laporan->fresh()->komentar_pengurus);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get('/admin/laporan')->assertOk()
            ->assertSee('Sedang ditangani petugas.')
            ->assertSee('Sudah dilaporkan ke petugas, menunggu perbaikan.')
            ->assertSee('laporan-foto');

        $this->actingAs($warga)
            ->get('/dashboard/laporan')->assertOk()
            ->assertSee('Sedang ditangani petugas.')
            ->assertSee('laporan-foto');
    }

    // --- SURAT -------------------------------------------------------------
    // Judul + alamat di PDF surat (dulu judul memakai map nama panjang padahal
    // jenis tersimpan sebagai slug, dan alamat selalu '—').
    public function test_pdf_surat_judul_dan_alamat_benar(): void
    {
        $surat = SuratPengajuan::where('status', 'disetujui')->firstOrFail();
        $pengaturan = PengaturanRt::firstOrFail();

        $this->view('surat.pdf', ['surat' => $surat, 'pengaturan' => $pengaturan])
            ->assertSee('SURAT KETERANGAN DOMISILI')
            ->assertSee('Bendan Kergon Gg.1 No. 45');
    }

    // Surat yang sudah ditolak tidak bisa disetujui lagi (dulu guard setujui
    // hanya menolak status 'disetujui').
    public function test_surat_yang_ditolak_tidak_bisa_disetujui(): void
    {
        $surat = SuratPengajuan::where('status', 'diajukan')->firstOrFail();
        $sekretaris = $this->user('sekretaris@rt02.example');

        $this->actingAs($sekretaris)
            ->post(route('admin.surat.tolak', $surat), ['alasan' => 'Data tidak lengkap'])
            ->assertRedirect();
        $this->assertEquals('ditolak', $surat->fresh()->status);

        $this->actingAs($sekretaris)
            ->post(route('admin.surat.setujui', $surat))
            ->assertRedirect();
        $this->assertEquals('ditolak', $surat->fresh()->status);
    }

    // --- IURAN -------------------------------------------------------------
    // Tagihan lunas tidak bisa dibayar ulang (dulu upload kedua membuat
    // pembayaran baru, dan verifikasi kedua menggandakan kas).
    public function test_tagihan_lunas_tidak_bisa_dibayar_ulang(): void
    {
        $warga = $this->user('warga1@rt02.example');
        $tagihan = $this->bayarDanVerifikasi($warga, '2026-12');
        $this->assertEquals('lunas', $tagihan->status);

        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->image('bukti2.jpg', 800, 600)->size(100),
            'nominal_dibayar' => $tagihan->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertEquals(1, Pembayaran::where('tagihan_id', $tagihan->id)->count());
        $this->assertEquals('lunas', $tagihan->fresh()->status);
    }

    // Nominal bayar dipaksa sesuai tagihan (dulu hidden field bisa diubah,
    // kas tercatat tidak sesuai tagihan).
    public function test_nominal_bayar_dipaksa_sesuai_tagihan(): void
    {
        $warga = $this->user('warga1@rt02.example');
        app(IuranService::class)->generateTagihan('2026-12');
        $tagihan = TagihanIuran::untukUser($warga)->where('periode', '2026-12')->firstOrFail();

        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg', 800, 600)->size(100),
            'nominal_dibayar' => 1000,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertRedirect();

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->assertEquals((int) $tagihan->nominal, (int) $pembayaran->nominal_dibayar);
    }

    // --- KAS ---------------------------------------------------------------
    // Kas dari pembayaran terverifikasi tidak bisa dihapus (dulu bisa dihapus,
    // tagihan tetap lunas tapi catatan kas hilang sehingga saldo tidak cocok).
    public function test_kas_dari_pembayaran_tidak_bisa_dihapus(): void
    {
        $warga = $this->user('warga1@rt02.example');
        $tagihan = $this->bayarDanVerifikasi($warga, '2026-12');
        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $kas = Kas::where('pembayaran_id', $pembayaran->id)->firstOrFail();
        $bendahara = $this->user('bendahara@rt02.example');

        $this->actingAs($bendahara)
            ->delete(route('admin.kas.destroy', $kas))
            ->assertRedirect();
        $this->assertDatabaseHas('kas', ['id' => $kas->id, 'deleted_at' => null]);

        // Kas manual (tanpa pembayaran) tetap bisa dihapus.
        $manual = Kas::whereNull('pembayaran_id')->firstOrFail();
        $this->actingAs($bendahara)
            ->delete(route('admin.kas.destroy', $manual))
            ->assertRedirect();
        $this->assertSoftDeleted('kas', ['id' => $manual->id]);
    }

    // --- OTORISASI IURAN/KAS ----------------------------------------------
    // Ketua boleh melihat, tapi tidak boleh generate tagihan, tambah tarif,
    // atau catat kas (tugas bendahara); tombol verifikasi disembunyikan.
    public function test_ketua_tidak_bisa_generate_tarif_dan_catat_kas(): void
    {
        $warga = $this->user('warga1@rt02.example');
        app(IuranService::class)->generateTagihan('2026-12');
        $tagihan = TagihanIuran::untukUser($warga)->where('periode', '2026-12')->firstOrFail();
        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg', 800, 600)->size(100),
            'nominal_dibayar' => $tagihan->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertRedirect();

        $ketua = $this->user('ketua@rt02.example');
        $this->actingAs($ketua)->get('/admin/iuran')->assertOk()
            ->assertDontSee('>Verifikasi</button>');

        $this->actingAs($ketua)
            ->post(route('admin.iuran.generate'), ['periode' => '2026-12'])
            ->assertForbidden();
        $this->actingAs($ketua)
            ->post(route('admin.iuran.tarif.store'), [
                'nama' => 'Iuran Uji', 'nominal' => 5000,
                'periode_mulai' => '2026-12-01', 'is_active' => 1,
            ])->assertForbidden();
        $this->actingAs($ketua)
            ->post(route('admin.kas.store'), [
                'jenis' => 'masuk', 'kategori' => 'Iuran', 'nominal' => 10000,
                'keterangan' => 'Tes ketua', 'tanggal' => now()->toDateString(),
            ])->assertForbidden();

        // Bendahara tetap boleh.
        $this->actingAs($this->user('bendahara@rt02.example'))
            ->post(route('admin.iuran.generate'), ['periode' => '2026-12'])
            ->assertRedirect();
    }
}
