<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadBuktiRequest;
use App\Models\Pembayaran;
use App\Models\TagihanIuran;
use App\Services\IuranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IuranController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $tagihan = TagihanIuran::untukUser($user)
            ->with(['tarif', 'pembayaran'])
            ->orderByDesc('periode')
            ->paginate(12);

        // Bulan berjalan: 1 tagihan per tarif aktif (Bulanan + Sampah).
        $tagihanBulanBerjalan = TagihanIuran::untukUser($user)
            ->where('periode', now()->format('Y-m'))
            ->with(['tarif', 'pembayaran'])
            ->orderBy('tarif_id')
            ->get();

        return view('dashboard.iuran.index', compact('tagihan', 'tagihanBulanBerjalan'));
    }

    public function bayar(TagihanIuran $tagihan, UploadBuktiRequest $request, IuranService $iuranService): RedirectResponse
    {
        $this->authorize('view', $tagihan);

        if (! in_array($tagihan->status, ['belum_bayar', 'terlambat'], true)) {
            return redirect()->route('dashboard.iuran.index')
                ->with('error', 'Tagihan ini sudah dibayar atau sedang menunggu verifikasi Bendahara.');
        }

        $iuranService->uploadBukti($tagihan, $request->validated(), $request->file('bukti'));

        return redirect()->route('dashboard.iuran.index')
            ->with('success', 'Bukti bayar berhasil diunggah dan menunggu verifikasi Bendahara.');
    }

    public function bukti(Pembayaran $pembayaran): BinaryFileResponse
    {
        $this->authorize('view', $pembayaran);

        abort_if(! $pembayaran->bukti_path || ! Storage::disk('public')->exists($pembayaran->bukti_path), 404);

        return response()->file(Storage::disk('public')->path($pembayaran->bukti_path));
    }
}
