<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuratRequest;
use App\Models\ActivityLog;
use App\Models\PengaturanRt;
use App\Models\SuratPengajuan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SuratController extends Controller
{
    public function index(): View
    {
        $surat = SuratPengajuan::where('diajukan_oleh_user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('dashboard.surat.index', compact('surat'));
    }

    public function create(): View
    {
        return view('dashboard.surat.create');
    }

    public function store(StoreSuratRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Akun tanpa warga terhubung (mis. Admin Sistem) tidak punya
        // warga_id — tolak dengan pesan jelas, jangan sampai 500 karena
        // kolom warga_id di surat_pengajuan NOT NULL.
        if (! $user->warga_id) {
            return redirect()->back()
                ->with('error', 'Akun Anda belum terhubung dengan data warga, jadi tidak bisa mengajukan surat. Gunakan akun warga, atau hubungi Sekretaris RT.');
        }

        $surat = SuratPengajuan::create([
            'warga_id' => $user->warga_id,
            'diajukan_oleh_user_id' => $user->id,
            'jenis' => $request->validated('jenis'),
            'keperluan' => $request->validated('keperluan'),
            'data_surat' => $request->validated('data_surat'),
            'status' => 'diajukan',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'aksi' => 'ajukan_surat',
            'tabel_terkait' => 'surat_pengajuan',
            'record_id' => $surat->id,
            'detail' => ['jenis' => $surat->jenis],
        ]);

        return redirect()->route('dashboard.surat.index')
            ->with('success', 'Pengajuan surat berhasil dikirim dan menunggu diproses pengurus.');
    }

    public function pdf(SuratPengajuan $surat)
    {
        $this->authorize('view', $surat);

        if ($surat->status !== 'disetujui') {
            return redirect()->route('dashboard.surat.index')
                ->with('error', 'Surat hanya dapat diunduh setelah disetujui.');
        }

        $pengaturan = PengaturanRt::first();

        $pdf = Pdf::loadView('surat.pdf', compact('surat', 'pengaturan'))
            ->setPaper('a4');

        return $pdf->download('surat-'.$surat->jenis.'-'.$surat->id.'.pdf');
    }
}
