<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengumumanRequest;
use App\Models\ActivityLog;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(): View
    {
        $pengumuman = Pengumuman::latest()->paginate(15);

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function create(): View
    {
        return view('admin.pengumuman.create');
    }

    public function store(StorePengumumanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['dibuat_oleh'] = auth()->id();
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('gambar')) {
            $data['gambar_path'] = $request->file('gambar')->store('pengumuman', 'public');
        }
        unset($data['gambar']);

        $pengumuman = Pengumuman::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_pengumuman',
            'tabel_terkait' => 'pengumuman',
            'record_id' => $pengumuman->id,
            'detail' => ['judul' => $pengumuman->judul],
        ]);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.edit', compact('pengumuman'));
    }

    public function update(StorePengumumanRequest $request, Pengumuman $pengumuman): RedirectResponse
    {
        $this->authorize('update', $pengumuman);

        $data = $request->validated();
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('gambar')) {
            $data['gambar_path'] = $request->file('gambar')->store('pengumuman', 'public');
        }
        unset($data['gambar']);

        $pengumuman->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'ubah_pengumuman',
            'tabel_terkait' => 'pengumuman',
            'record_id' => $pengumuman->id,
            'detail' => ['judul' => $pengumuman->judul],
        ]);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $this->authorize('delete', $pengumuman);

        $pengumuman->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_pengumuman',
            'tabel_terkait' => 'pengumuman',
            'record_id' => $pengumuman->id,
            'detail' => ['judul' => $pengumuman->judul],
        ]);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
