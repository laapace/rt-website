<?php

namespace Tests\Feature;

use App\Models\JadwalRonda;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtBugfix7Test extends TestCase
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

    // --- Ronda: (tanggal, blok) ganda harus ditolak validasi, bukan 500 -----------
    public function test_ronda_ganda_tanggal_blok_ditolak_validasi(): void
    {
        $warga = Warga::firstOrFail();

        // Seed sudah punya jadwal 2026-11-06 Blok A.
        $this->assertEquals(1, JadwalRonda::whereDate('tanggal', '2026-11-06')->where('blok', 'Blok A')->count());

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.ronda.store'), [
                'tanggal' => '2026-11-06',
                'blok' => 'Blok A',
                'keterangan' => 'Duplikat.',
                'petugas' => [$warga->id],
            ])->assertRedirect()
            ->assertSessionHasErrors('tanggal');
        $this->assertEquals(1, JadwalRonda::whereDate('tanggal', '2026-11-06')->where('blok', 'Blok A')->count());

        // Kombinasi baru tetap bisa dibuat.
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.ronda.store'), [
                'tanggal' => '2026-11-06',
                'blok' => 'Blok C',
                'keterangan' => 'Baru.',
                'petugas' => [$warga->id],
            ])->assertRedirect(route('admin.ronda.index'));
        $this->assertEquals(1, JadwalRonda::whereDate('tanggal', '2026-11-06')->where('blok', 'Blok C')->count());
    }

    // --- Hapus akun: ada riwayat -> ditolak dengan pesan, tetap login --------------
    public function test_hapus_akun_dengan_riwayat_ditolak_dengan_pesan(): void
    {
        // warga1 punya surat di seed (diajukan_oleh_user_id = warga1).
        $warga1 = $this->user('warga1@rt02.example');

        $this->actingAs($warga1)
            ->delete(route('profile.destroy'), ['password' => 'password123'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('error', 'Akun tidak bisa dihapus karena masih memiliki riwayat (surat, laporan, pembayaran, atau lainnya). Hubungi Admin RT bila akun ini memang harus dihapus.');
        $this->assertDatabaseHas('users', ['email' => 'warga1@rt02.example']);
        $this->assertAuthenticatedAs($warga1);
    }

    public function test_hapus_akun_tanpa_riwayat_berhasil(): void
    {
        $this->post('/register', [
            'name' => 'Hapus Saya',
            'email' => 'hapus@example.com',
            'no_kk' => '3273010101010006',
            'nik' => '3273010101000019',
            'no_hp' => '081234567894',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $baru = User::where('email', 'hapus@example.com')->firstOrFail();
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertRedirect();

        $this->actingAs($baru)
            ->delete(route('profile.destroy'), ['password' => 'password123'])
            ->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['email' => 'hapus@example.com']);
        $this->assertGuest();
    }
}
