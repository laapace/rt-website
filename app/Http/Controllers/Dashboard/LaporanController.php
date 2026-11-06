<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\ActivityLog;
use App\Models\LaporanWarga;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        $laporan = LaporanWarga::where('dilaporkan_oleh_user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('dashboard.laporan.index', compact('laporan'));
    }

    public function create(): View
    {
        return view('dashboard.laporan.create');
    }

    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        // Akun tanpa warga terhubung (mis. Admin Sistem) tidak punya
        // warga_id — tolak dengan pesan jelas, jangan sampai 500 karena
        // kolom warga_id di laporan_warga NOT NULL.
        if (! $user->warga_id) {
            return redirect()->back()
                ->with('error', 'Akun Anda belum terhubung dengan data warga, jadi tidak bisa membuat laporan. Gunakan akun warga, atau hubungi Sekretaris RT.');
        }

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('laporan-foto', 'public');
        }
        unset($data['foto']);

        $laporan = LaporanWarga::create(array_merge($data, [
            'warga_id' => $user->warga_id,
            'dilaporkan_oleh_user_id' => $user->id,
            'status' => 'baru',
        ]));

        ActivityLog::create([
            'user_id' => $user->id,
            'aksi' => 'buat_laporan',
            'tabel_terkait' => 'laporan_warga',
            'record_id' => $laporan->id,
            'detail' => ['kategori' => $laporan->kategori, 'judul' => $laporan->judul],
        ]);

        return redirect()->route('dashboard.laporan.index')
            ->with('success', 'Laporan berhasil dikirim dan akan ditindaklanjuti pengurus.');
    }
}
