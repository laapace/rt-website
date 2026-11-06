<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\LaporanWarga;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\SuratPengajuan;
use App\Models\TagihanIuran;
use App\Models\Warga;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $periodeBerjalan = now()->format('Y-m');
        $notifikasi = Notifikasi::where('user_id', $user->id)->latest()->take(5)->get();

        if ($user->hasRole('warga') && ! $user->hasRole('ketua', 'sekretaris', 'bendahara', 'admin')) {
            // Bulan berjalan punya 1 tagihan per tarif aktif (Bulanan + Sampah).
            $tagihanBulanIniList = TagihanIuran::untukUser($user)
                ->where('periode', $periodeBerjalan)
                ->with('tarif')
                ->get();
            $tagihanBulanIni = $tagihanBulanIniList->first();

            $suratTerakhir = SuratPengajuan::where('diajukan_oleh_user_id', $user->id)
                ->latest()
                ->first();

            $laporanTerbuka = LaporanWarga::where('dilaporkan_oleh_user_id', $user->id)
                ->whereIn('status', ['baru', 'diproses'])
                ->count();

            $rondaBerikutnya = JadwalRonda::where('tanggal', '>=', now()->toDateString())
                ->when($user->warga_id, function ($query) use ($user) {
                    $query->whereHas('petugas', function ($sub) use ($user) {
                        $sub->where('warga.id', $user->warga_id);
                    });
                })
                ->orderBy('tanggal')
                ->first();

            // Status gabungan: belum bayar mengalahkan menunggu, menunggu
            // mengalahkan lunas; nominal = total semua tarif bulan ini.
            $statusGabungan = $tagihanBulanIniList->isEmpty() ? null
                : ($tagihanBulanIniList->contains(fn ($t) => in_array($t->status, ['belum_bayar', 'terlambat'], true))
                    ? 'Belum Bayar'
                    : ($tagihanBulanIniList->contains(fn ($t) => $t->status === 'menunggu_verifikasi')
                        ? 'Menunggu Verifikasi'
                        : 'Lunas'));
            $stats = [
                'tagihan_bulan_ini' => $tagihanBulanIniList->sum('nominal') ?: null,
                'tagihan_status' => $statusGabungan,
                'surat_terakhir' => $suratTerakhir ? ucfirst(str_replace('_', ' ', $suratTerakhir->jenis)) : null,
                'surat_status' => $suratTerakhir ? match ($suratTerakhir->status) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    'diproses' => 'Diproses',
                    default => 'Diajukan',
                } : null,
                'laporan_terbuka' => $laporanTerbuka,
                'ronda_berikutnya' => $rondaBerikutnya?->tanggal,
                // blok sudah berisi 'Blok A' dst — jangan ditambah 'Blok ' lagi.
                'ronda_keterangan' => $rondaBerikutnya?->blok,
            ];
            $pengumuman = Pengumuman::where('is_published', true)->orderByDesc('is_pinned')->latest()->take(3)->get();

            return view('dashboard.index', compact(
                'stats', 'pengumuman', 'tagihanBulanIni', 'suratTerakhir', 'laporanTerbuka', 'rondaBerikutnya', 'notifikasi'
            ));
        }

        // Dashboard pengurus (ketua / sekretaris / bendahara / admin).
        $totalKk = KartuKeluarga::where('status_tinggal', 'aktif')->count();
        $totalWarga = Warga::where('status_tinggal', 'aktif')->count();

        try {
            $ringkasan = DB::table('v_iuran_periode')->where('periode', $periodeBerjalan)->first();
            $iuranTerkumpul = $ringkasan->terkumpul ?? 0;
        } catch (\Throwable) {
            // Fallback kalau view v_iuran_periode belum dibuat — hitung langsung.
            $iuranTerkumpul = TagihanIuran::where('periode', $periodeBerjalan)
                ->where('status', 'lunas')
                ->sum('nominal');
        }

        $tunggakan = TagihanIuran::where('periode', $periodeBerjalan)
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->count();

        $suratMenunggu = SuratPengajuan::whereIn('status', ['diajukan', 'diproses'])->count();
        $laporanTerbuka = LaporanWarga::whereIn('status', ['baru', 'diproses'])->count();

        $rondaBerikutnya = JadwalRonda::where('tanggal', '>=', now()->toDateString())->orderBy('tanggal')->first();
        $stats = [
            'tagihan_bulan_ini' => $iuranTerkumpul,
            'tagihan_status' => $tunggakan.' KK belum bayar bulan ini',
            'surat_terakhir' => $suratMenunggu.' menunggu',
            'surat_status' => 'Surat diajukan / diproses',
            'laporan_terbuka' => $laporanTerbuka,
            'ronda_berikutnya' => $rondaBerikutnya?->tanggal,
            // blok sudah berisi 'Blok A' dst — jangan ditambah 'Blok ' lagi.
            'ronda_keterangan' => $rondaBerikutnya?->blok,
        ];
        $pengumuman = Pengumuman::where('is_published', true)->orderByDesc('is_pinned')->latest()->take(3)->get();

        return view('dashboard.index', compact(
            'stats', 'pengumuman', 'totalKk', 'totalWarga', 'iuranTerkumpul', 'tunggakan',
            'suratMenunggu', 'laporanTerbuka', 'notifikasi', 'periodeBerjalan'
        ));
    }
}
