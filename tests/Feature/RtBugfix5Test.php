<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtBugfix5Test extends TestCase
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

    private function inactiveUser(): User
    {
        return User::factory()->create([
            'name' => 'Warga Baru',
            'email' => 'wargabaru@example.com',
            'is_active' => false,
        ]);
    }

    // --- (a) Sekretaris bisa buka halaman + mengaktifkan -------------------------
    public function test_sekretaris_bisa_buka_halaman_pengguna_dan_mengaktifkan(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $baru = $this->inactiveUser();

        $this->actingAs($sekretaris)->get(route('admin.pengguna.index'))->assertOk()
            ->assertSee($baru->name)
            ->assertSee('Menunggu Aktivasi')
            ->assertSee('Aktifkan');

        $this->actingAs($sekretaris)->post(route('admin.pengguna.aktifkan', $baru))
            ->assertRedirect(route('admin.pengguna.index'));
        $this->assertTrue($baru->fresh()->is_active);
    }

    // --- (b) Sekretaris tidak melihat tombol kelola peran --------------------------
    public function test_sekretaris_tidak_melihat_tombol_kelola_peran(): void
    {
        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->get(route('admin.pengguna.index'))->assertOk()
            ->assertDontSee('name="peran"', false)
            ->assertDontSee('Tambah');

        $this->actingAs($this->user('admin@rt02.example'))
            ->get(route('admin.pengguna.index'))->assertOk()
            ->assertSee('name="peran"', false);
    }

    // --- (c) Sekretaris tidak bisa tambah/hapus peran (403) ------------------------
    public function test_sekretaris_tidak_bisa_tambah_atau_hapus_peran(): void
    {
        $sekretaris = $this->user('sekretaris@rt02.example');
        $warga = $this->user('warga1@rt02.example');

        $this->actingAs($sekretaris)->post(route('admin.pengguna.peran.tambah', $warga), [
            'peran' => 'ketua',
        ])->assertForbidden();
        $this->assertDatabaseMissing('user_roles', ['user_id' => $warga->id, 'peran' => 'ketua']);

        $this->actingAs($sekretaris)->delete(route('admin.pengguna.peran.hapus', $sekretaris), [
            'peran' => 'sekretaris',
        ])->assertForbidden();
        $this->assertTrue($sekretaris->fresh()->hasRole('sekretaris'));
    }

    // --- Warga & Ketua tetap tidak bisa buka halaman pengguna -----------------------
    public function test_warga_dan_ketua_tidak_bisa_buka_halaman_pengguna(): void
    {
        $baru = $this->inactiveUser();

        $this->actingAs($this->user('warga1@rt02.example'))
            ->get(route('admin.pengguna.index'))->assertForbidden();
        $this->actingAs($this->user('warga1@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertForbidden();
        $this->assertFalse($baru->fresh()->is_active);

        $this->actingAs($this->user('ketua@rt02.example'))
            ->get(route('admin.pengguna.index'))->assertForbidden();
    }

    // --- Alur lengkap: daftar -> Sekretaris aktifkan -> bisa login ------------------
    public function test_daftar_lalu_sekretaris_mengaktifkan_lalu_bisa_login(): void
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
        $this->assertFalse($baru->is_active);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.pengguna.aktifkan', $baru))->assertRedirect();
        $this->assertTrue($baru->fresh()->is_active);

        $this->post('/login', [
            'email' => 'tahlilbaru@example.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');
    }
}
