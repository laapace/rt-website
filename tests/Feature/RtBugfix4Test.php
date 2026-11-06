<?php

namespace Tests\Feature;

use App\Models\KartuKeluarga;
use App\Models\LaporanWarga;
use App\Models\SuratPengajuan;
use App\Models\TagihanIuran;
use App\Models\TarifIuran;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\RtSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtBugfix4Test extends TestCase
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

    private function wargaUser(): User
    {
        return $this->user('warga1@rt02.example');
    }

    // --- SURAT: jenis selalu "tidak valid" ------------------------------------
    public function test_buat_surat_lewat_form(): void
    {
        $this->actingAs($this->wargaUser())
            ->get(route('dashboard.surat.create'))->assertOk()
            ->assertSee('value="domisili"', false)
            ->assertSee('value="lainnya"', false);

        $this->actingAs($this->wargaUser())
            ->post(route('dashboard.surat.store'), [
                'jenis' => 'domisili',
                'keperluan' => 'Untuk pendaftaran sekolah anak.',
                'data_surat' => ['catatan' => 'Mohon diproses.'],
            ])->assertRedirect();

        $surat = SuratPengajuan::where('keperluan', 'Untuk pendaftaran sekolah anak.')->firstOrFail();
        $this->assertEquals('domisili', $surat->jenis);
        $this->assertEquals('diajukan', $surat->status);
        $this->assertEquals('Mohon diproses.', $surat->data_surat['catatan']);
    }

    // --- LAPORAN: kategori selalu "tidak valid" --------------------------------
    public function test_buat_laporan_lewat_form(): void
    {
        $this->actingAs($this->wargaUser())
            ->get(route('dashboard.laporan.create'))->assertOk()
            ->assertSee('value="kebersihan"', false)
            ->assertSee('value="lainnya"', false);

        $this->actingAs($this->wargaUser())
            ->post(route('dashboard.laporan.store'), [
                'kategori' => 'kebersihan',
                'judul' => 'Sampah menumpuk',
                'deskripsi' => 'Sampah menumpuk di gang.',
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])->assertRedirect();

        $laporan = LaporanWarga::where('judul', 'Sampah menumpuk')->firstOrFail();
        $this->assertEquals('kebersihan', $laporan->kategori);
        $this->assertNotNull($laporan->foto_path);
    }

    // --- LABEL: slug jangan tampil mentah --------------------------------------
    public function test_label_jenis_surat_dan_kategori_laporan_tampil(): void
    {
        $this->actingAs($this->wargaUser())->post(route('dashboard.surat.store'), [
            'jenis' => 'domisili',
            'keperluan' => 'Untuk pendaftaran sekolah.',
        ]);
        $this->actingAs($this->wargaUser())->post(route('dashboard.laporan.store'), [
            'kategori' => 'kebersihan',
            'judul' => 'Sampah menumpuk',
            'deskripsi' => 'Sampah menumpuk di gang.',
        ]);

        $this->actingAs($this->wargaUser())->get(route('dashboard.surat.index'))->assertOk()
            ->assertSee('Surat Keterangan Domisili');
        $this->actingAs($this->wargaUser())->get(route('dashboard.laporan.index'))->assertOk()
            ->assertSee('Kebersihan');
        $this->actingAs($this->user('sekretaris@rt02.example'))->get(route('admin.surat.index'))->assertOk()
            ->assertSee('Surat Keterangan Domisili');
        $this->actingAs($this->user('sekretaris@rt02.example'))->get(route('admin.laporan.index'))->assertOk()
            ->assertSee('Kebersihan');
    }

    // --- PENGGUNA: kelola peran tidak punya UI ---------------------------------
    public function test_admin_dapat_menambah_dan_menghapus_peran(): void
    {
        $admin = $this->user('admin@rt02.example');
        $warga = $this->wargaUser();

        $this->actingAs($admin)->get(route('admin.pengguna.index'))->assertOk()
            ->assertSee('name="peran"', false);

        $this->actingAs($admin)->post(route('admin.pengguna.peran.tambah', $warga), [
            'peran' => 'ketua',
        ])->assertRedirect();
        $this->assertDatabaseHas('user_roles', ['user_id' => $warga->id, 'peran' => 'ketua']);
        $this->assertTrue($warga->fresh()->hasRole('ketua'));

        $this->actingAs($admin)->delete(route('admin.pengguna.peran.hapus', $warga), [
            'peran' => 'ketua',
        ])->assertRedirect();
        $this->assertDatabaseMissing('user_roles', ['user_id' => $warga->id, 'peran' => 'ketua']);
    }

    public function test_admin_tidak_bisa_menghapus_peran_admin_milik_sendiri(): void
    {
        $admin = $this->user('admin@rt02.example');

        $this->actingAs($admin)->delete(route('admin.pengguna.peran.hapus', $admin), [
            'peran' => 'admin',
        ])->assertRedirect();
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    // --- FILTER PENGUMUMAN ------------------------------------------------------
    public function test_filter_pengumuman_kategori_berfungsi(): void
    {
        $this->get('/pengumuman?kategori=kegiatan')->assertOk()
            ->assertSee('Kerja Bakti');
        $this->get('/pengumuman?kategori=keuangan')->assertOk()
            ->assertDontSee('Kerja Bakti');
    }

    // --- RESET PASSWORD ---------------------------------------------------------
    public function test_reset_password_request_mengirim_notifikasi(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'warga1@rt02.example'])
            ->assertRedirect();

        Notification::assertSentTo($this->wargaUser(), ResetPassword::class);
    }

    // --- PROFIL -----------------------------------------------------------------
    public function test_update_profil_dan_ganti_password(): void
    {
        $warga = $this->wargaUser();

        $this->actingAs($warga)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => 'warga1@rt02.example',
        ])->assertRedirect();
        $this->assertEquals('Budi Santoso', $warga->fresh()->name);

        $this->actingAs($warga)->put('/password', [
            'current_password' => 'password123',
            'password' => 'rahasia-baru-99',
            'password_confirmation' => 'rahasia-baru-99',
        ])->assertRedirect();

        $this->post('/logout');
        $this->post('/login', ['email' => 'warga1@rt02.example', 'password' => 'rahasia-baru-99'])
            ->assertRedirect('/dashboard');
    }

    // --- ANGGOTA KELUARGA --------------------------------------------------------
    public function test_tambah_edit_hapus_anggota_lewat_form(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $kk = KartuKeluarga::firstOrFail();

        $this->actingAs($sekretaris)->post(route('admin.warga.anggota.store', $kk), [
            'kartu_keluarga_id' => $kk->id,
            'nik' => '3273010101000098',
            'nama_lengkap' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'hubungan_keluarga' => 'Istri',
            'status_tinggal' => 'aktif',
        ])->assertRedirect();
        $anggota = Warga::where('nik', '3273010101000098')->firstOrFail();

        $this->actingAs($sekretaris)->put(route('admin.warga.anggota.update', $anggota), [
            'kartu_keluarga_id' => $kk->id,
            'nik' => '3273010101000098',
            'nama_lengkap' => 'Siti Aminah Wijaya',
            'jenis_kelamin' => 'P',
            'hubungan_keluarga' => 'Istri',
            'status_tinggal' => 'aktif',
        ])->assertRedirect();
        $this->assertEquals('Siti Aminah Wijaya', $anggota->fresh()->nama_lengkap);

        $this->actingAs($sekretaris)->delete(route('admin.warga.anggota.destroy', $anggota))
            ->assertRedirect();
        $this->assertSoftDeleted('warga', ['id' => $anggota->id]);
    }

    // --- IURAN: generate + tarif -------------------------------------------------
    public function test_generate_tagihan_dan_tambah_tarif_oleh_bendahara(): void
    {
        $bendahara = $this->user('bendahara@rt02.example');

        $this->actingAs($bendahara)->post(route('admin.iuran.generate'), [
            'bulan' => 12,
            'tahun' => 2026,
        ])->assertRedirect();
        $this->assertGreaterThan(0, TagihanIuran::where('periode', '2026-12')->count());

        $this->actingAs($bendahara)->post(route('admin.iuran.tarif.store'), [
            'nama' => 'Iuran Keamanan',
            'nominal' => 5000,
            'periode_mulai' => '2026-12-01',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('tarif_iuran', ['nama' => 'Iuran Keamanan', 'nominal' => 5000]);
    }

    // --- KAS: export --------------------------------------------------------------
    public function test_kas_export_berhasil(): void
    {
        $this->actingAs($this->user('bendahara@rt02.example'))
            ->get(route('admin.kas.export', ['bulan' => '2026-11']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    // --- SURAT: tolak dengan alasan -------------------------------------------------
    public function test_tolak_surat_dengan_alasan(): void
    {
        $this->actingAs($this->wargaUser())->post(route('dashboard.surat.store'), [
            'jenis' => 'usaha',
            'keperluan' => 'Untuk pengajuan kredit usaha.',
        ]);
        $surat = SuratPengajuan::where('jenis', 'usaha')->firstOrFail();

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.surat.tolak', $surat), ['alasan' => 'Data usaha belum lengkap.'])
            ->assertRedirect();
        $this->assertEquals('ditolak', $surat->fresh()->status);
        $this->assertEquals('Data usaha belum lengkap.', $surat->fresh()->alasan_penolakan);
    }
}
