<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LaporanWarga;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanAdminController extends Controller
{
    public function index(Request $request): View
    {
        $laporan = LaporanWarga::with('warga')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('kategori'), function ($query) use ($request) {
                $query->where('kategori', $request->string('kategori'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.laporan.index', compact('laporan'));
    }

    public function updateStatus(Request $request, LaporanWarga $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        $request->validate([
            'status' => ['required', 'in:baru,diproses,selesai'],
            'komentar_pengurus' => ['nullable', 'string'],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ]);

        $laporan->update([
            'status' => $request->string('status'),
            'komentar_pengurus' => $request->string('komentar_pengurus')->toString() ?: $laporan->komentar_pengurus,
            'ditangani_oleh' => auth()->id(),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'ubah_status_laporan',
            'tabel_terkait' => 'laporan_warga',
            'record_id' => $laporan->id,
            'detail' => ['status' => $laporan->status],
        ]);

        Notifikasi::create([
            'user_id' => $laporan->dilaporkan_oleh_user_id,
            'judul' => 'Status laporanmu berubah: '.$laporan->judul,
            'isi' => 'Laporanmu kini berstatus '.$laporan->status.'.',
            'tautan' => '/dashboard/laporan',
        ]);

        return redirect()->route('admin.laporan.index')
            ->with('success', 'Status laporan berhasil diperbarui.');
    }
}
