<?php

namespace Tests\Feature;

use App\Models\KartuKeluarga;
use App\Models\Pengumuman;
use App\Models\SuratPengajuan;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtCrawlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RtSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_semua_halaman_publik_200(): void
    {
        foreach (['/', '/profil', '/kontak', '/pengumuman', '/agenda', '/login', '/register'] as $url) {
            $this->get($url)->assertOk($url);
        }
        $p = Pengumuman::firstOrFail();
        $this->get('/pengumuman/'.$p->id)->assertOk();
    }

    public function test_semua_halaman_warga_200(): void
    {
        $this->actingAs($this->user('warga1@rt02.example'));
        foreach (['/dashboard', '/dashboard/iuran', '/dashboard/surat', '/dashboard/surat/create', '/dashboard/laporan', '/dashboard/laporan/create', '/dashboard/jadwal-ronda', '/dashboard/keluarga', '/dashboard/notifikasi'] as $url) {
            $this->get($url)->assertOk($url);
        }
        $s = SuratPengajuan::where('status', 'disetujui')->firstOrFail();
        $this->get('/dashboard/surat/'.$s->id.'/pdf')->assertOk();
    }

    public function test_semua_halaman_admin_200(): void
    {
        $this->actingAs($this->user('sekretaris@rt02.example'));
        $kk = KartuKeluarga::firstOrFail();
        $anggota = Warga::firstOrFail();
        foreach ([
            '/admin/warga', '/admin/warga/'.$kk->id, '/admin/warga/create', '/admin/warga/'.$kk->id.'/edit',
            '/admin/warga/'.$kk->id.'/anggota/create', '/admin/warga/anggota/'.$anggota->id.'/edit',
            '/admin/pengumuman', '/admin/pengumuman/create', '/admin/agenda', '/admin/agenda/create',
            '/admin/surat', '/admin/laporan', '/admin/ronda',
        ] as $url) {
            $this->get($url)->assertOk($url);
        }
        $this->actingAs($this->user('bendahara@rt02.example'));
        foreach (['/admin/iuran', '/admin/kas', '/admin/kas/export'] as $url) {
            $this->get($url)->assertOk($url);
        }
        $this->actingAs($this->user('admin@rt02.example'));
        $this->get('/admin/pengguna')->assertOk();
        $this->get('/admin/warga')->assertOk();
        $this->get('/admin/iuran')->assertOk();
    }

    public function test_register_warga_baru_tidak_langsung_aktif(): void
    {
        $warga = Warga::where('hubungan_keluarga', 'Anak')->firstOrFail();
        $kk = $warga->kartuKeluarga;
        $resp = $this->post('/register', [
            'name' => 'Warga Baru Tes',
            'email' => 'wargabaru@rt02.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'no_kk' => $kk->no_kk,
            'nik' => $warga->nik,
            'no_hp' => '081234567890',
        ]);
        $resp->assertRedirect('/login');
        $baru = User::where('email', 'wargabaru@rt02.example')->firstOrFail();
        $this->assertFalse((bool) $baru->is_active);
        $this->assertTrue($baru->hasRole('warga'));
        // Belum bisa login sebelum diaktifkan
        $this->post('/login', ['email' => 'wargabaru@rt02.example', 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        // Akun baru harus tampil di Kelola Pengguna (regresi: daftar sempat
        // kosong karena controller mengirim $pengguna, view memakai $users).
        $this->actingAs($this->user('admin@rt02.example'))
            ->get('/admin/pengguna')->assertOk()
            ->assertSee('Warga Baru Tes')
            ->assertSee('wargabaru@rt02.example')
            ->assertSee($kk->no_kk)
            ->assertSee('Menunggu Aktivasi')
            ->assertSee('Aktifkan')
            ->assertDontSee('Belum ada pengguna');
        // Sekretaris/admin aktifkan, lalu bisa login
        $this->actingAs($this->user('admin@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertRedirect();
        $this->assertTrue((bool) $baru->fresh()->is_active);
        $this->post('/login', ['email' => 'wargabaru@rt02.example', 'password' => 'password123'])
            ->assertRedirect('/dashboard');
    }
}
