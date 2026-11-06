<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKasRequest;
use App\Models\ActivityLog;
use App\Models\Kas;
use App\Services\KasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KasController extends Controller
{
    public function index(Request $request, KasService $kasService): View
    {
        $saldo = $kasService->saldo();

        $kas = Kas::query()
            ->when($request->filled('bulan'), function ($query) use ($request) {
                // Kompatibel MySQL & SQLite (tidak memakai DATE_FORMAT).
                $awal = $request->string('bulan')->toString().'-01';
                $akhir = date('Y-m-t', strtotime($awal));
                $query->whereBetween('tanggal', [$awal, $akhir]);
            })
            ->when($request->filled('jenis'), function ($query) use ($request) {
                $query->where('jenis', $request->string('jenis'));
            })
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kas.index', compact('saldo', 'kas'));
    }

    public function store(StoreKasRequest $request): RedirectResponse
    {
        $this->authorize('create', Kas::class);

        $kas = Kas::create(array_merge($request->validated(), [
            'dicatat_oleh' => auth()->id(),
        ]));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_kas',
            'tabel_terkait' => 'kas',
            'record_id' => $kas->id,
            'detail' => ['jenis' => $kas->jenis, 'nominal' => $kas->nominal, 'kategori' => $kas->kategori],
        ]);

        return redirect()->route('admin.kas.index')
            ->with('success', 'Catatan kas berhasil ditambahkan.');
    }

    public function destroy(Kas $kas): RedirectResponse
    {
        $this->authorize('delete', $kas);

        // Kas yang berasal dari pembayaran terverifikasi tidak boleh dihapus:
        // tagihannya sudah lunas, menghapus kas membuat saldo tidak cocok
        // dengan tagihan. Koreksi dilakukan dengan menolak pembayaran terkait.
        if ($kas->pembayaran_id) {
            return redirect()->route('admin.kas.index')
                ->with('error', 'Kas dari pembayaran terverifikasi tidak dapat dihapus.');
        }

        $kas->delete(); // Soft-delete.

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_kas',
            'tabel_terkait' => 'kas',
            'record_id' => $kas->id,
            'detail' => ['jenis' => $kas->jenis, 'nominal' => $kas->nominal],
        ]);

        return redirect()->route('admin.kas.index')
            ->with('success', 'Catatan kas berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Kas::class);

        $kas = Kas::query()
            ->when($request->filled('bulan'), function ($query) use ($request) {
                // Kompatibel MySQL & SQLite (tidak memakai DATE_FORMAT).
                $awal = $request->string('bulan')->toString().'-01';
                $akhir = date('Y-m-t', strtotime($awal));
                $query->whereBetween('tanggal', [$awal, $akhir]);
            })
            ->orderBy('tanggal')
            ->get(['tanggal', 'jenis', 'kategori', 'keterangan', 'nominal']);

        $namaFile = 'kas-rt-'.($request->string('bulan')->toString() ?: now()->format('Y-m')).'.csv';

        return response()->streamDownload(function () use ($kas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['tanggal', 'jenis', 'kategori', 'keterangan', 'nominal']);
            foreach ($kas as $baris) {
                fputcsv($handle, [
                    $baris->tanggal,
                    $baris->jenis,
                    $baris->kategori,
                    $baris->keterangan,
                    $baris->nominal,
                ]);
            }
            fclose($handle);
        }, $namaFile, ['Content-Type' => 'text/csv']);
    }
}
