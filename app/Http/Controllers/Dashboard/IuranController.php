<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadBuktiRequest;
use App\Models\Pembayaran;
use App\Models\TagihanIuran;
use App\Services\IuranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IuranController extends Controller
{
    public function index(Request $request): View
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

        // Tagihan yang dipilih dari riwayat (?bayar=<id>): tampilkan kartu
        // bayarnya di atas riwayat. Tanpa ini, tagihan di luar bulan
        // berjalan tidak bisa dibayar sama sekali (riwayat hanya "—").
        // Scope untukUser + status menjaga agar hanya tagihan milik sendiri
        // yang masih bisa dibayar yang bisa dipilih.
        $tagihanDipilih = null;
        if ($request->filled('bayar')) {
            $tagihanDipilih = TagihanIuran::untukUser($user)
                ->where('id', $request->string('bayar'))
                ->whereIn('status', ['belum_bayar', 'terlambat'])
                ->with(['tarif', 'pembayaran'])
                ->first();
        }

        return view('dashboard.iuran.index', compact('tagihan', 'tagihanBulanBerjalan', 'tagihanDipilih'));
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
