<?php

namespace Tests\Feature\Auth;

use App\Models\KartuKeluarga;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Sesuai FR-02 / design §4.4: register wajib No. KK + NIK yang cocok
        // dengan data warga, dan akun TIDAK langsung aktif (menunggu Sekretaris).
        $kk = KartuKeluarga::create([
            'no_kk' => '3273010101010099', 'alamat' => 'Jl. Contoh No. 99',
            'blok' => 'Blok A', 'rt' => '02', 'rw' => '03', 'status_tinggal' => 'aktif',
        ]);
        $warga = Warga::create([
            'kartu_keluarga_id' => $kk->id, 'nik' => '3273010101010099',
            'nama_lengkap' => 'Test User', 'jenis_kelamin' => 'L',
            'hubungan_keluarga' => 'Kepala Keluarga', 'status_tinggal' => 'aktif',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'no_kk' => $kk->no_kk,
            'nik' => $warga->nik,
            'no_hp' => '081234567890',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertFalse((bool) $user->is_active);
        $this->assertTrue($user->hasRole('warga'));
        $this->assertEquals($kk->id, $user->kartu_keluarga_id);
    }

    public function test_register_ditolak_bila_kk_atau_nik_tidak_cocok(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'no_kk' => '3273010101010099',
            'nik' => '3273010101010099',
        ]);

        $response->assertSessionHasErrors(['no_kk']);
        $this->assertGuest();
    }
}
