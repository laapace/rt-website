<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanRt;
use App\Models\SuratPengajuan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuratAdminController extends Controller
{
    public function index(): View
    {
        $antrean = SuratPengajuan::whereIn('status', ['diajukan', 'diproses'])
            ->with('warga')
            ->latest()
            ->paginate(10, ['*'], 'antrean');

        $riwayat = SuratPengajuan::whereIn('status', ['disetujui', 'ditolak'])
            ->with('warga')
            ->latest()
            ->paginate(10, ['*'], 'riwayat');

        return view('admin.surat.index', compact('antrean', 'riwayat'));
    }

    public function setujui(SuratPengajuan $surat, SuratService $suratService): RedirectResponse
    {
        $this->authorize('update', $surat);

        // Log & notifikasi ditulis di dalam Service — jangan dobel log di sini.
        $nomor = $suratService->setujuiSurat($surat);

        return redirect()->route('admin.surat.index')
            ->with('success', "Surat berhasil disetujui dengan nomor {$nomor}.");
    }

    public function tolak(Request $request, SuratPengajuan $surat, SuratService $suratService): RedirectResponse
    {
        $this->authorize('update', $surat);

        $request->validate([
            'alasan' => ['required', 'string'],
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $suratService->tolakSurat($surat, $request->string('alasan')->toString());

        return redirect()->route('admin.surat.index')
            ->with('success', 'Pengajuan surat ditolak.');
    }

    public function pdf(SuratPengajuan $surat)
    {
        $this->authorize('view', $surat);

        if ($surat->status !== 'disetujui') {
            return redirect()->route('admin.surat.index')
                ->with('error', 'Surat hanya dapat diunduh setelah disetujui.');
        }

        $pengaturan = PengaturanRt::first();

        $pdf = Pdf::loadView('surat.pdf', compact('surat', 'pengaturan'))
            ->setPaper('a4');

        return $pdf->download('surat-'.$surat->jenis.'-'.$surat->id.'.pdf');
    }
}
