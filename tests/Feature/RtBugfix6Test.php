<?php

namespace Tests\Feature;

use App\Models\LaporanWarga;
use App\Models\SuratPengajuan;
use App\Models\TagihanIuran;
use App\Models\User;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtBugfix6Test extends TestCase
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

    // --- 500 warga_id NULL: Admin Sistem tidak punya warga terhubung -------------
    public function test_admin_tidak_bisa_ajukan_surat_dan_laporan_tanpa_500(): void
    {
        $admin = $this->user('admin@rt02.example');
        $this->assertNull($admin->warga_id);

        $this->actingAs($admin)->post(route('dashboard.surat.store'), [
            'jenis' => 'domisili',
            'keperluan' => 'Tes sebagai admin.',
        ])->assertRedirect()
            ->assertSessionHas('error', 'Akun Anda belum terhubung dengan data warga, jadi tidak bisa mengajukan surat. Gunakan akun warga, atau hubungi Sekretaris RT.');
        $this->assertDatabaseCount('surat_pengajuan', 2); // hanya 2 dari seeder

        $this->actingAs($admin)->post(route('dashboard.laporan.store'), [
            'kategori' => 'kebersihan',
            'judul' => 'Tes sebagai admin',
            'deskripsi' => 'Deskripsi tes.',
        ])->assertRedirect()
            ->assertSessionHas('error', 'Akun Anda belum terhubung dengan data warga, jadi tidak bisa membuat laporan. Gunakan akun warga, atau hubungi Sekretaris RT.');
        $this->assertDatabaseCount('laporan_warga', 3); // hanya 3 dari seeder
    }

    public function test_halaman_buat_memperingatkan_akun_tanpa_warga(): void
    {
        $this->actingAs($this->user('admin@rt02.example'))
            ->get(route('dashboard.surat.create'))->assertOk()
            ->assertSee('belum terhubung dengan data warga');
        $this->actingAs($this->user('admin@rt02.example'))
            ->get(route('dashboard.laporan.create'))->assertOk()
            ->assertSee('belum terhubung dengan data warga');

        $this->actingAs($this->user('warga1@rt02.example'))
            ->get(route('dashboard.surat.create'))->assertOk()
            ->assertDontSee('belum terhubung dengan data warga');
        $this->actingAs($this->user('warga1@rt02.example'))
            ->get(route('dashboard.laporan.create'))->assertOk()
            ->assertDontSee('belum terhubung dengan data warga');
    }

    // --- Alur lengkap: daftar -> aktifkan (Sekretaris) -> ajukan surat/laporan -----
    public function test_akun_baru_daftar_bisa_ajukan_surat_dan_laporan(): void
    {
        $this->post('/register', [
            'name' => 'Tahlil Baru',
            'email' => 'tahlilbaru@example.com',
            'no_kk' => '3273010101010004',
            'nik' => '3273010101000012',
            'no_hp' => '081234567899',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $baru = User::where('email', 'tahlilbaru@example.com')->firstOrFail();
        $this->assertNotNull($baru->warga_id, 'Registrasi harus menautkan warga_id dari NIK');
        $this->assertEquals('3273010101000012', $baru->warga->nik);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertRedirect();

        $this->actingAs($baru)->post(route('dashboard.surat.store'), [
            'jenis' => 'domisili',
            'keperluan' => 'Untuk pendaftaran sekolah.',
        ])->assertRedirect();
        $this->assertDatabaseHas('surat_pengajuan', [
            'warga_id' => $baru->warga_id,
            'diajukan_oleh_user_id' => $baru->id,
            'jenis' => 'domisili',
        ]);

        $this->actingAs($baru)->post(route('dashboard.laporan.store'), [
            'kategori' => 'kebersihan',
            'judul' => 'Sampah menumpuk',
            'deskripsi' => 'Sampah menumpuk di gang.',
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertRedirect();
        $this->assertDatabaseHas('laporan_warga', [
            'warga_id' => $baru->warga_id,
            'dilaporkan_oleh_user_id' => $baru->id,
            'kategori' => 'kebersihan',
        ]);
    }

    // --- Bayar iuran tidak bergantung pada warga_id --------------------------------
    public function test_admin_tetap_bisa_bayar_iuran(): void
    {
        $tagihan = TagihanIuran::where('status', 'belum_bayar')->firstOrFail();

        $this->actingAs($this->user('admin@rt02.example'))
            ->post(route('dashboard.iuran.bayar', $tagihan), [
                'nominal_dibayar' => $tagihan->nominal,
                'metode' => 'transfer',
                'tanggal_bayar' => '2026-11-06',
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])->assertRedirect();
        $this->assertEquals('menunggu_verifikasi', $tagihan->fresh()->status);
    }

    // --- Riwayat iuran: tagihan belum bayar di luar bulan berjalan bisa dibayar ----
    private function daftarDanAktifkanKk0006(): User
    {
        $this->post('/register', [
            'name' => 'Eko Baru',
            'email' => 'ekobaru@example.com',
            'no_kk' => '3273010101010006',
            'nik' => '3273010101000018',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $baru = User::where('email', 'ekobaru@example.com')->firstOrFail();
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertRedirect();

        return $baru->fresh();
    }

    public function test_riwayat_tagihan_belum_bayar_punya_tombol_bayar_dan_bisa_dibayar(): void
    {
        $eko = $this->daftarDanAktifkanKk0006();
        $belumBayar = TagihanIuran::where('kartu_keluarga_id', $eko->kartu_keluarga_id)
            ->where('periode', '2026-11')->where('status', 'belum_bayar')->firstOrFail();

        // Riwayat menampilkan tombol Bayar untuk tagihan yang belum dibayar.
        $this->actingAs($eko)->get(route('dashboard.iuran.index'))->assertOk()
            ->assertSee('bayar='.$belumBayar->id, false);

        // Membuka ?bayar=<id> menampilkan kartu bayar dengan formulirnya.
        $this->actingAs($eko)->get(route('dashboard.iuran.index', ['bayar' => $belumBayar->id]))->assertOk()
            ->assertSee('Bayar Tagihan')
            ->assertSee('Upload Bukti Bayar');

        // Bayar tagihan bulan November (bukan bulan berjalan di semua konteks)
        // berhasil seperti tagihan bulan berjalan.
        $this->actingAs($eko)->post(route('dashboard.iuran.bayar', $belumBayar), [
            'nominal_dibayar' => $belumBayar->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => '2026-11-06',
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect();
        $this->assertEquals('menunggu_verifikasi', $belumBayar->fresh()->status);
    }

    public function test_bayar_param_tidak_bisa_memilih_tagihan_orang_lain_atau_yang_lunas(): void
    {
        $eko = $this->daftarDanAktifkanKk0006();

        // Tagihan lunas (KK 0006 tidak punya yang lunas, pakai tagihan lunas
        // mana pun): kartu bayar tidak tampil karena statusnya bukan
        // belum_bayar/terlambat.
        $lunas = TagihanIuran::where('status', 'lunas')->firstOrFail();
        $this->actingAs($eko)->get(route('dashboard.iuran.index', ['bayar' => $lunas->id]))->assertOk()
            ->assertDontSee('Bayar Tagihan');

        // Tagihan belum bayar milik KK lain: kartu bayar tidak tampil.
        $orangLain = TagihanIuran::where('status', 'belum_bayar')
            ->where('kartu_keluarga_id', '!=', $eko->kartu_keluarga_id)->firstOrFail();
        $this->actingAs($eko)->get(route('dashboard.iuran.index', ['bayar' => $orangLain->id]))->assertOk()
            ->assertDontSee('Bayar Tagihan');
    }

    // --- Status laporan: form harus mengirim PATCH (route-nya PATCH) ----------------
    public function test_form_simpan_status_laporan_mengandung_method_patch(): void
    {
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get(route('admin.laporan.index'))->assertOk()
            ->assertSee('name="_method" value="PATCH"', false);
    }

    public function test_simpan_status_laporan_lewat_form_browser(): void
    {
        $laporan = LaporanWarga::where('status', 'baru')->firstOrFail();

        // Browser hanya bisa mengirim POST; form yang benar menyertakan
        // _method=PATCH agar sampai ke route PATCH. Tanpa itu: 405.
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.laporan.status', $laporan), [
                '_method' => 'PATCH',
                'status' => 'diproses',
                'komentar_pengurus' => 'Sedang ditangani.',
            ])->assertRedirect(route('admin.laporan.index'));
        $this->assertEquals('diproses', $laporan->fresh()->status);
        $this->assertEquals('Sedang ditangani.', $laporan->fresh()->komentar_pengurus);
    }
}
