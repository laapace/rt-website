<?php

use App\Http\Controllers\Admin\AgendaController as AdminAgendaController;
use App\Http\Controllers\Admin\IuranAdminController;
use App\Http\Controllers\Admin\KasController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PengumumanController as AdminPengumumanController;
use App\Http\Controllers\Admin\RondaAdminController;
use App\Http\Controllers\Admin\SuratAdminController;
use App\Http\Controllers\Admin\WargaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\IuranController;
use App\Http\Controllers\Dashboard\KeluargaController;
use App\Http\Controllers\Dashboard\LaporanController;
use App\Http\Controllers\Dashboard\NotifikasiController;
use App\Http\Controllers\Dashboard\RondaController;
use App\Http\Controllers\Dashboard\SuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\AgendaController as PublicAgendaController;
use App\Http\Controllers\Public\BerandaController;
use App\Http\Controllers\Public\PengumumanController as PublicPengumumanController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Publik (tanpa login)
// ---------------------------------------------------------------------------
Route::get('/', [BerandaController::class, 'index'])->name('public.beranda');
Route::get('/profil', [BerandaController::class, 'profil'])->name('public.profil');
Route::get('/kontak', [BerandaController::class, 'kontak'])->name('public.kontak');
Route::get('/pengumuman', [PublicPengumumanController::class, 'index'])->name('public.pengumuman.index');
Route::get('/pengumuman/{pengumuman}', [PublicPengumumanController::class, 'show'])->name('public.pengumuman.show');
Route::get('/agenda', [PublicAgendaController::class, 'index'])->name('public.agenda.index');

// ---------------------------------------------------------------------------
// Dashboard warga & pengurus (login)
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Iuran warga
        Route::get('/iuran', [IuranController::class, 'index'])->name('iuran.index');
        Route::post('/iuran/{tagihan}/bayar', [IuranController::class, 'bayar'])->name('iuran.bayar');
        Route::get('/iuran/bukti/{pembayaran}', [IuranController::class, 'bukti'])->name('iuran.bukti');

        // Surat warga
        Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::get('/surat/{surat}/pdf', [SuratController::class, 'pdf'])->name('surat.pdf');

        // Laporan warga
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/create', [LaporanController::class, 'create'])->name('laporan.create');
        Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');

        // Jadwal ronda & keluarga
        Route::get('/jadwal-ronda', [RondaController::class, 'index'])->name('ronda.index');
        Route::get('/keluarga', [KeluargaController::class, 'index'])->name('keluarga.index');

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
        Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'tandaiBaca'])->name('notifikasi.baca');
    });

    // -----------------------------------------------------------------------
    // Admin: data warga, pengumuman, agenda, surat, laporan, ronda
    // (sekretaris / ketua / admin)
    // -----------------------------------------------------------------------
    Route::middleware('role:sekretaris,ketua,admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('warga', WargaController::class)->parameters(['warga' => 'warga']);
        Route::get('/warga/{kartuKeluarga}/anggota/create', [WargaController::class, 'tambahAnggota'])->name('warga.anggota.create');
        Route::post('/warga/{kartuKeluarga}/anggota', [WargaController::class, 'storeAnggota'])->name('warga.anggota.store');
        Route::get('/warga/anggota/{anggota}/edit', [WargaController::class, 'editAnggota'])->name('warga.anggota.edit');
        Route::put('/warga/anggota/{anggota}', [WargaController::class, 'updateAnggota'])->name('warga.anggota.update');
        Route::delete('/warga/anggota/{anggota}', [WargaController::class, 'hapusAnggota'])->name('warga.anggota.destroy');

        Route::resource('pengumuman', AdminPengumumanController::class)->except(['show']);
        Route::resource('agenda', AdminAgendaController::class)->except(['show']);

        Route::get('/surat', [SuratAdminController::class, 'index'])->name('surat.index');
        Route::post('/surat/{surat}/setujui', [SuratAdminController::class, 'setujui'])->name('surat.setujui');
        Route::post('/surat/{surat}/tolak', [SuratAdminController::class, 'tolak'])->name('surat.tolak');
        Route::get('/surat/{surat}/pdf', [SuratAdminController::class, 'pdf'])->name('surat.pdf');

        Route::get('/laporan', [LaporanAdminController::class, 'index'])->name('laporan.index');
        Route::patch('/laporan/{laporan}/status', [LaporanAdminController::class, 'updateStatus'])->name('laporan.status');

        Route::get('/ronda', [RondaAdminController::class, 'index'])->name('ronda.index');
        Route::post('/ronda', [RondaAdminController::class, 'store'])->name('ronda.store');
        Route::delete('/ronda/{ronda}', [RondaAdminController::class, 'destroy'])->name('ronda.destroy');
    });

    // -----------------------------------------------------------------------
    // Admin: iuran & kas (bendahara / ketua / admin)
    // -----------------------------------------------------------------------
    Route::middleware('role:bendahara,ketua,admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/iuran', [IuranAdminController::class, 'index'])->name('iuran.index');
        Route::post('/iuran/tarif', [IuranAdminController::class, 'storeTarif'])->name('iuran.tarif.store');
        Route::post('/iuran/generate', [IuranAdminController::class, 'generate'])->name('iuran.generate');
        Route::post('/iuran/verifikasi/{pembayaran}', [IuranAdminController::class, 'verifikasi'])->name('iuran.verifikasi');
        Route::post('/iuran/tolak/{pembayaran}', [IuranAdminController::class, 'tolak'])->name('iuran.tolak');

        Route::get('/kas', [KasController::class, 'index'])->name('kas.index');
        Route::post('/kas', [KasController::class, 'store'])->name('kas.store');
        Route::delete('/kas/{kas}', [KasController::class, 'destroy'])->name('kas.destroy');
        Route::get('/kas/export', [KasController::class, 'export'])->name('kas.export');
    });

    // -----------------------------------------------------------------------
    // Admin Sistem: manajemen pengguna (admin saja)
    // -----------------------------------------------------------------------
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])->name('pengguna.aktifkan');
        Route::post('/pengguna/{pengguna}/peran', [PenggunaController::class, 'tambahPeran'])->name('pengguna.peran.tambah');
        Route::delete('/pengguna/{pengguna}/peran', [PenggunaController::class, 'hapusPeran'])->name('pengguna.peran.hapus');
    });
});

require __DIR__.'/auth.php';
