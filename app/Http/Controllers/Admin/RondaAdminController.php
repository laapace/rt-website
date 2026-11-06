<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRondaRequest;
use App\Models\ActivityLog;
use App\Models\JadwalRonda;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RondaAdminController extends Controller
{
    public function index(): View
    {
        $jadwal = JadwalRonda::with('petugas')
            ->orderBy('tanggal')
            ->paginate(15);

        // Daftar warga aktif untuk dipilih sebagai petugas (multi-select).
        $daftarWarga = Warga::where('status_tinggal', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.ronda.index', compact('jadwal', 'daftarWarga'));
    }

    public function store(StoreRondaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $jadwal = JadwalRonda::create([
            'tanggal' => $data['tanggal'],
            'blok' => $data['blok'],
            'keterangan' => $data['keterangan'] ?? null,
            'dibuat_oleh' => auth()->id(),
        ]);

        if (! empty($data['petugas'])) {
            $jadwal->petugas()->sync($data['petugas']);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_ronda',
            'tabel_terkait' => 'jadwal_ronda',
            'record_id' => $jadwal->id,
            'detail' => ['tanggal' => $jadwal->tanggal, 'blok' => $jadwal->blok],
        ]);

        return redirect()->route('admin.ronda.index')
            ->with('success', 'Jadwal ronda berhasil ditambahkan.');
    }

    public function destroy(JadwalRonda $ronda): RedirectResponse
    {
        $this->authorize('delete', $ronda);

        $ronda->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_ronda',
            'tabel_terkait' => 'jadwal_ronda',
            'record_id' => $ronda->id,
            'detail' => ['tanggal' => $ronda->tanggal, 'blok' => $ronda->blok],
        ]);

        return redirect()->route('admin.ronda.index')
            ->with('success', 'Jadwal ronda berhasil dihapus.');
    }
}
