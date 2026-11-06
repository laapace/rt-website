<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTarifRequest;
use App\Models\ActivityLog;
use App\Models\Pembayaran;
use App\Models\TagihanIuran;
use App\Models\TarifIuran;
use App\Services\IuranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IuranAdminController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $request->string('periode')->toString() ?: now()->format('Y-m');

        $rowRingkasan = DB::table('v_iuran_periode')->where('periode', $periode)->first();
        // View mengembalikan array — DB::table mengembalikan stdClass.
        $ringkasan = $rowRingkasan ? (array) $rowRingkasan : [
            'periode' => $periode, 'total_tagihan' => 0, 'lunas' => 0,
            'menunggu' => 0, 'belum_bayar' => 0, 'terkumpul' => 0,
        ];

        $antrean = Pembayaran::where('status', 'menunggu_verifikasi')
            ->with(['tagihan.kartuKeluarga'])
            ->latest()
            ->paginate(10, ['*'], 'antrean');

        $tagihan = TagihanIuran::with(['kartuKeluarga', 'tarif'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('periode'), function ($query) use ($request) {
                $query->where('periode', $request->string('periode'));
            })
            ->orderByDesc('periode')
            ->paginate(15)
            ->withQueryString();

        // $tarifs: semua tarif aktif (RT 02: Iuran Bulanan + Iuran Sampah).
        // $tarif: tarif aktif terbaru, dipakai sebagai nilai awal form tambah tarif.
        $tarifs = TarifIuran::where('is_active', true)->orderBy('periode_mulai')->get();
        $tarif = $tarifs->last();

        return view('admin.iuran.index', compact('periode', 'ringkasan', 'antrean', 'tagihan', 'tarif', 'tarifs'));
    }

    public function storeTarif(StoreTarifRequest $request): RedirectResponse
    {
        $tarif = TarifIuran::create($request->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_tarif',
            'tabel_terkait' => 'tarif_iuran',
            'record_id' => $tarif->id,
            'detail' => ['nama' => $tarif->nama, 'nominal' => $tarif->nominal],
        ]);

        return redirect()->route('admin.iuran.index')
            ->with('success', 'Tarif iuran berhasil ditambahkan.');
    }

    public function generate(Request $request, IuranService $iuranService): RedirectResponse
    {
        // Form UI mengirim bulan+tahun terpisah; API/test bisa mengirim 'periode' langsung.
        if ($request->filled('periode')) {
            $request->validate(['periode' => ['required', 'date_format:Y-m']]);
            $periode = $request->string('periode')->toString();
        } else {
            $request->validate([
                'bulan' => ['required', 'integer', 'min:1', 'max:12'],
                'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            ]);
            $periode = sprintf('%04d-%02d', (int) $request->integer('tahun'), (int) $request->integer('bulan'));
        }

        $hasil = $iuranService->generateTagihan($periode);

        return redirect()->route('admin.iuran.index', ['periode' => $periode])
            ->with('success', "Tagihan berhasil dibuat: {$hasil['dibuat']} tagihan baru, {$hasil['sudah_ada']} sudah ada sebelumnya.");
    }

    public function verifikasi(Pembayaran $pembayaran, IuranService $iuranService): RedirectResponse
    {
        $this->authorize('update', $pembayaran);

        // Log & kas ditulis di dalam Service — jangan dobel log di sini.
        $iuranService->verifikasiPembayaran($pembayaran, true, null);

        return redirect()->route('admin.iuran.index')
            ->with('success', 'Pembayaran berhasil diverifikasi dan kas bertambah.');
    }

    public function tolak(Request $request, Pembayaran $pembayaran, IuranService $iuranService): RedirectResponse
    {
        $this->authorize('update', $pembayaran);

        $request->validate([
            'alasan' => ['required', 'string'],
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $iuranService->verifikasiPembayaran($pembayaran, false, $request->string('alasan')->toString());

        return redirect()->route('admin.iuran.index')
            ->with('success', 'Pembayaran ditolak dan warga dapat mengunggah ulang bukti bayar.');
    }
}
