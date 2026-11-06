<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\KartuKeluarga;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtBugfix3Test extends TestCase
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

    // --- POLICY HILANG: edit/hapus selalu 403 --------------------------------
    public function test_edit_dan_hapus_pengumuman_oleh_sekretaris(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $pengumuman = Pengumuman::firstOrFail();

        $this->actingAs($sekretaris)
            ->put(route('admin.pengumuman.update', $pengumuman), [
                'judul' => 'Judul diubah',
                'isi' => $pengumuman->isi,
                'kategori' => $pengumuman->kategori,
            ])->assertRedirect();
        $this->assertEquals('Judul diubah', $pengumuman->fresh()->judul);

        $this->actingAs($sekretaris)
            ->delete(route('admin.pengumuman.destroy', $pengumuman))
            ->assertRedirect();
        $this->assertDatabaseMissing('pengumuman', ['id' => $pengumuman->id]);
    }

    public function test_edit_dan_hapus_agenda_oleh_sekretaris(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $agenda = Agenda::firstOrFail();

        $this->actingAs($sekretaris)
            ->put(route('admin.agenda.update', $agenda), [
                'judul' => 'Agenda diubah',
                'deskripsi' => $agenda->deskripsi,
                'tanggal_mulai' => '2026-12-05 08:00:00',
                'tanggal_selesai' => '2026-12-05 10:00:00',
                'lokasi' => $agenda->lokasi,
            ])->assertRedirect();
        $this->assertEquals('Agenda diubah', $agenda->fresh()->judul);

        $this->actingAs($sekretaris)
            ->delete(route('admin.agenda.destroy', $agenda))
            ->assertRedirect();
        $this->assertDatabaseMissing('agenda', ['id' => $agenda->id]);
    }

    public function test_edit_dan_hapus_kk_oleh_sekretaris_juga_menghapus_anggota(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $kk = KartuKeluarga::firstOrFail();
        $anggotaIds = $kk->warga()->pluck('id')->all();
        $this->assertNotEmpty($anggotaIds);

        $this->actingAs($sekretaris)
            ->put(route('admin.warga.update', $kk), [
                'no_kk' => $kk->no_kk,
                'alamat' => 'Alamat baru No. 99',
                'blok' => $kk->blok,
                'rt' => $kk->rt,
                'rw' => $kk->rw,
                'status_tinggal' => 'aktif',
            ])->assertRedirect();
        $this->assertEquals('Alamat baru No. 99', $kk->fresh()->alamat);

        $this->actingAs($sekretaris)
            ->delete(route('admin.warga.destroy', $kk))
            ->assertRedirect();
        $this->assertSoftDeleted('kartu_keluarga', ['id' => $kk->id]);
        foreach ($anggotaIds as $id) {
            $this->assertSoftDeleted('warga', ['id' => $id]);
        }
    }

    // --- FORM WARGA: kepala keluarga tidak pernah dibuat -----------------------
    public function test_buat_kk_dengan_kepala_keluarga(): void
    {
        // Form harus mengirim nama kepala dengan field 'nama_kepala'
        // (dulu field-nya bernama 'kepala_keluarga' sehingga nama terbuang).
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get(route('admin.warga.create'))->assertOk()
            ->assertSee('name="nama_kepala"', false);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.warga.store'), [
                'no_kk' => '3273010101010099',
                'alamat' => 'Bendan Kergon Gg.1 No. 99',
                'blok' => 'Blok A',
                'rt' => '02',
                'rw' => '03',
                'status_tinggal' => 'aktif',
                'nama_kepala' => 'Budi Santoso',
                'nik_kepala' => '3273010101000099',
            ])->assertRedirect(route('admin.warga.show', KartuKeluarga::where('no_kk', '3273010101010099')->firstOrFail()));

        $kk = KartuKeluarga::where('no_kk', '3273010101010099')->firstOrFail();
        $this->assertNotNull($kk->kepala_keluarga_id);
        $kepala = Warga::findOrFail($kk->kepala_keluarga_id);
        $this->assertEquals('Budi Santoso', $kepala->nama_lengkap);
        $this->assertEquals('Kepala Keluarga', $kepala->hubungan_keluarga);
    }

    // --- FORM AGENDA: tanggal/waktu tidak pernah tersimpan ---------------------
    public function test_buat_agenda_lewat_form(): void
    {
        // Form harus memakai field 'tanggal_mulai'/'tanggal_selesai'
        // (dulu 'tanggal'/'waktu' sehingga validasi selalu gagal).
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get(route('admin.agenda.create'))->assertOk()
            ->assertSee('name="tanggal_mulai"', false)
            ->assertSee('name="tanggal_selesai"', false);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.agenda.store'), [
                'judul' => 'Kerja Bakti Desember',
                'deskripsi' => 'Membersihkan selokan.',
                'tanggal_mulai' => '2026-12-06T07:00',
                'tanggal_selesai' => '2026-12-06T10:00',
                'lokasi' => 'Balai RT',
            ])->assertRedirect();

        $agenda = Agenda::where('judul', 'Kerja Bakti Desember')->firstOrFail();
        $this->assertEquals('2026-12-06 07:00:00', $agenda->tanggal_mulai->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-12-06 10:00:00', $agenda->tanggal_selesai->format('Y-m-d H:i:s'));
    }

    // --- FORM PENGUMUMAN: pin tidak pernah tersimpan ---------------------------
    public function test_pin_pengumuman_lewat_form(): void
    {
        // Checkbox harus bernama 'is_pinned' (dulu 'is_penting'), dan opsi
        // kategori harus lowercase (dulu 'Umum' sehingga validasi gagal).
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get(route('admin.pengumuman.create'))->assertOk()
            ->assertSee('name="is_pinned"', false)
            ->assertSee('value="umum"', false);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.pengumuman.store'), [
                'judul' => 'Pengumuman penting',
                'isi' => 'Isi pengumuman.',
                'kategori' => 'umum',
                'is_pinned' => '1',
            ])->assertRedirect();

        $pengumuman = Pengumuman::where('judul', 'Pengumuman penting')->firstOrFail();
        $this->assertTrue((bool) $pengumuman->is_pinned);
    }

    // --- TAMPILAN PUBLIK -------------------------------------------------------
    public function test_halaman_profil_menampilkan_pengurus(): void
    {
        $this->get('/profil')->assertOk()
            ->assertSee('Abdullah')
            ->assertSee('Ketua')
            ->assertSee('Rina')
            ->assertSee('Ayu')
            ->assertSee('0812-3345-6677');
    }

    public function test_halaman_beranda_menampilkan_tanggal_agenda_benar(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('08 Nov 2026')
            ->assertSee('Kerja Bakti');
    }

    public function test_halaman_agenda_menampilkan_tanggal_dan_waktu_benar(): void
    {
        $this->get('/agenda')->assertOk()
            ->assertSee('08 Nov 2026')
            ->assertSee('07:00');
    }

    public function test_pengumuman_penting_dan_gambar_tampil(): void
    {
        $pengumuman = Pengumuman::firstOrFail();
        $pengumuman->update(['is_pinned' => true, 'gambar_path' => 'pengumuman/contoh.jpg']);

        $this->get(route('public.pengumuman.show', $pengumuman))->assertOk()
            ->assertSee('Penting')
            ->assertSee('storage/pengumuman/contoh.jpg')
            ->assertSee($pengumuman->dibuatOleh->name);
    }

    // --- HELPER + PESAN ERROR --------------------------------------------------
    public function test_format_tanggal_null_menampilkan_strip(): void
    {
        $this->assertEquals('—', formatTanggal(null));
        $this->assertEquals('—', formatTanggal(''));
        $this->assertEquals('08 Nov 2026', formatTanggal('2026-11-08'));
    }

    public function test_login_gagal_menampilkan_pesan_bahasa_indonesia(): void
    {
        $this->post('/login', ['email' => 'warga1@rt02.example', 'password' => 'salah'])
            ->assertRedirect();
        $this->get('/login')->assertOk()
            ->assertSee('Email atau kata sandi salah')
            ->assertDontSee('auth.failed');
    }

    public function test_login_terkunci_setelah_5x_salah(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/login', ['email' => 'warga1@rt02.example', 'password' => 'salah'.$i]);
        }
        $this->get('/login')->assertOk()
            ->assertSee('Terlalu banyak')
            ->assertDontSee('auth.throttle');
    }

    // --- DASHBOARD: isi notifikasi tidak pernah tampil -------------------------
    public function test_isi_notifikasi_tampil_di_dashboard(): void
    {
        $warga = $this->user('warga1@rt02.example');
        Notifikasi::create([
            'user_id' => $warga->id,
            'judul' => 'Tagihan baru',
            'isi' => 'Tagihan Iuran Bulanan periode 2026-12 sudah diterbitkan.',
            'tautan' => '/dashboard/iuran',
        ]);

        $this->actingAs($warga)->get('/dashboard')->assertOk()
            ->assertSee('Tagihan baru')
            ->assertSee('Tagihan Iuran Bulanan periode 2026-12 sudah diterbitkan.');
    }
}
