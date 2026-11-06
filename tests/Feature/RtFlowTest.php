<?php

namespace Tests\Feature;

use App\Models\KartuKeluarga;
use App\Models\Kas;
use App\Models\Pembayaran;
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

class RtFlowTest extends TestCase
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

    public function test_beranda_dan_login_publik_bisa_diakses(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/pengumuman')->assertOk();
        $this->get('/profil')->assertOk();
        $this->get('/agenda')->assertOk();
        $this->get('/kontak')->assertOk();
    }

    public function test_login_keempat_peran_berhasil_dan_dashboard_200(): void
    {
        foreach (['ketua@rt02.example', 'sekretaris@rt02.example', 'bendahara@rt02.example', 'admin@rt02.example', 'warga1@rt02.example'] as $email) {
            $resp = $this->post('/login', ['email' => $email, 'password' => 'password123']);
            $resp->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertOk();
            $this->post('/logout');
        }
    }

    public function test_warga_tidak_bisa_buka_admin_kas_dan_admin_warga(): void
    {
        $this->actingAs($this->user('warga1@rt02.example'));
        $this->get('/admin/kas')->assertForbidden();
        $this->get('/admin/warga')->assertForbidden();
        $this->get('/admin/iuran')->assertForbidden();
        $this->get('/admin/pengguna')->assertForbidden();
    }

    public function test_tamu_tidak_bisa_buka_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_alur_bayar_iuran_dan_verifikasi_bendahara(): void
    {
        $warga = $this->user('warga1@rt02.example');
        app(IuranService::class)->generateTagihan('2026-12');
        $tagihan = TagihanIuran::untukUser($warga)->where('periode', '2026-12')->firstOrFail();
        $saldoAwal = Kas::where('jenis', 'masuk')->sum('nominal');

        $this->actingAs($warga)
            ->post(route('dashboard.iuran.bayar', $tagihan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg', 800, 600)->size(100),
                'nominal_dibayar' => $tagihan->nominal,
                'metode' => 'transfer',
                'tanggal_bayar' => now()->toDateString(),
            ])->assertRedirect();
        $this->assertDatabaseHas('pembayaran', ['tagihan_id' => $tagihan->id, 'status' => 'menunggu_verifikasi']);
        $this->assertEquals('menunggu_verifikasi', $tagihan->fresh()->status);

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->actingAs($this->user('bendahara@rt02.example'))
            ->post(route('admin.iuran.verifikasi', $pembayaran))
            ->assertRedirect();
        $this->assertEquals('terverifikasi', $pembayaran->fresh()->status);
        $this->assertEquals('lunas', $tagihan->fresh()->status);
        $this->assertDatabaseHas('kas', ['pembayaran_id' => $pembayaran->id, 'jenis' => 'masuk', 'nominal' => $tagihan->nominal]);
        $this->assertGreaterThan($saldoAwal, Kas::where('jenis', 'masuk')->sum('nominal'));
    }

    public function test_upload_bukti_lebih_dari_2mb_dan_format_salah_ditolak(): void
    {
        $warga = $this->user('warga1@rt02.example');
        app(IuranService::class)->generateTagihan('2026-12');
        $tagihan = TagihanIuran::untukUser($warga)->where('periode', '2026-12')->firstOrFail();
        $jumlahAwal = Pembayaran::where('tagihan_id', $tagihan->id)->count();

        // File 3 MB (fake image size 3072 KB)
        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->image('besar.jpg')->size(3072),
            'nominal_dibayar' => $tagihan->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertSessionHasErrors('bukti');

        // Format .exe
        $this->actingAs($warga)->post(route('dashboard.iuran.bayar', $tagihan), [
            'bukti' => UploadedFile::fake()->create('jahat.exe', 100, 'application/octet-stream'),
            'nominal_dibayar' => $tagihan->nominal,
            'metode' => 'transfer',
            'tanggal_bayar' => now()->toDateString(),
        ])->assertSessionHasErrors('bukti');

        $this->assertEquals($jumlahAwal, Pembayaran::where('tagihan_id', $tagihan->id)->count());
    }

    public function test_generate_tagihan_dua_kali_tidak_ganda(): void
    {
        $svc = app(IuranService::class);
        // 2 tarif aktif x 10 KK = 20 tagihan per periode.
        $r1 = $svc->generateTagihan('2026-12');
        $r2 = $svc->generateTagihan('2026-12');
        $this->assertEquals(20, $r1['dibuat']);
        $this->assertEquals(0, $r2['dibuat']);
        $this->assertEquals(20, $r2['sudah_ada']);
        $this->assertEquals(20, TagihanIuran::where('periode', '2026-12')->count());
    }

    public function test_alur_surat_sampai_pdf(): void
    {
        $warga = $this->user('warga1@rt02.example');
        $this->actingAs($warga)->post(route('dashboard.surat.store'), [
            'jenis' => 'domisili',
            'keperluan' => 'Untuk keperluan tes fitur surat',
        ])->assertRedirect();
        $surat = SuratPengajuan::where('diajukan_oleh_user_id', $warga->id)->where('keperluan', 'Untuk keperluan tes fitur surat')->firstOrFail();
        $this->assertEquals('diajukan', $surat->status);

        $this->actingAs($this->user('sekretaris@rt02.example'))
            ->post(route('admin.surat.setujui', $surat))
            ->assertRedirect();
        $surat->refresh();
        $this->assertEquals('disetujui', $surat->status);
        $this->assertNotNull($surat->nomor_surat);
        $this->assertMatchesRegularExpression('#^\d{3}/RT\.02/RW\.03/[IVX]+/\d{4}$#', $surat->nomor_surat);

        $resp = $this->actingAs($warga)->get(route('dashboard.surat.pdf', $surat));
        $resp->assertOk();
        $this->assertStringStartsWith('%PDF', $resp->getContent());
    }

    public function test_warga_hanya_melihat_tagihan_kk_sendiri(): void
    {
        $warga = $this->user('warga1@rt02.example');
        $semua = TagihanIuran::untukUser($warga)->pluck('kartu_keluarga_id')->unique();
        $this->assertEquals([$warga->kartu_keluarga_id], $semua->values()->all());
        $this->assertEquals(4, TagihanIuran::untukUser($warga)->count()); // 2 periode x 2 tarif
    }

    public function test_nik_tersamar(): void
    {
        $w = Warga::firstOrFail();
        $this->assertEquals('****'.substr($w->nik, -4), $w->nik_tersamar);
    }

    public function test_admin_warga_crud_dan_kas_export(): void
    {
        $this->actingAs($this->user('sekretaris@rt02.example'));
        $this->get(route('admin.warga.index'))->assertOk();
        $kk = KartuKeluarga::firstOrFail();
        $this->get(route('admin.warga.show', $kk))->assertOk();

        $this->actingAs($this->user('bendahara@rt02.example'));
        $this->get(route('admin.kas.index'))->assertOk();
        $resp = $this->get(route('admin.kas.export'));
        $resp->assertOk();
        $this->assertStringContainsString('tanggal', strtolower($resp->streamedContent()));
    }
}
