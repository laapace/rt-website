<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKartuKeluargaRequest;
use App\Http\Requests\StoreWargaRequest;
use App\Http\Requests\UpdateKartuKeluargaRequest;
use App\Http\Requests\UpdateWargaRequest;
use App\Models\ActivityLog;
use App\Models\KartuKeluarga;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WargaController extends Controller
{
    public function index(Request $request): View
    {
        $kk = KartuKeluarga::query()
            ->withCount('warga')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($q) {
                    $sub->where('no_kk', 'like', $q)
                        ->orWhere('alamat', 'like', $q)
                        ->orWhereHas('warga', function ($w) use ($q) {
                            $w->where('nama_lengkap', 'like', $q)
                                ->orWhere('nik', 'like', $q);
                        });
                });
            })
            ->when($request->filled('blok'), function ($query) use ($request) {
                $query->where('blok', 'like', '%'.$request->string('blok').'%');
            })
            ->when($request->filled('status_tinggal'), function ($query) use ($request) {
                $query->where('status_tinggal', $request->string('status_tinggal'));
            })
            ->orderBy('blok')->orderBy('no_kk')
            ->paginate(15)
            ->withQueryString();

        return view('admin.warga.index', compact('kk'));
    }

    public function show(KartuKeluarga $warga): View
    {
        $kartuKeluarga = $warga->load('warga');
        $log = ActivityLog::whereIn('tabel_terkait', ['kartu_keluarga', 'warga'])
            ->where(function ($query) use ($kartuKeluarga) {
                $query->where('record_id', $kartuKeluarga->id)
                    ->orWhereIn('record_id', $kartuKeluarga->warga->pluck('id'));
            })
            ->latest()
            ->take(20)
            ->get();

        $kk = $kartuKeluarga;
        $anggota = $kartuKeluarga->warga;

        return view('admin.warga.show', compact('kk', 'anggota', 'kartuKeluarga', 'log'));
    }

    public function create(): View
    {
        return view('admin.warga.create');
    }

    public function store(StoreKartuKeluargaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Langkah 1: buat KK (kepala_keluarga_id masih NULL — FK melingkar, schema.md §7).
        $kartuKeluarga = KartuKeluarga::create([
            'no_kk' => $data['no_kk'],
            'alamat' => $data['alamat'],
            'blok' => $data['blok'],
            'rt' => $data['rt'],
            'rw' => $data['rw'],
            'status_tinggal' => $data['status_tinggal'],
        ]);

        // Langkah 2: buat kepala keluarga lalu tautkan sebagai kepala KK.
        if (! empty($data['nama_kepala']) && ! empty($data['nik_kepala'])) {
            $kepala = Warga::create([
                'kartu_keluarga_id' => $kartuKeluarga->id,
                'nik' => $data['nik_kepala'],
                'nama_lengkap' => $data['nama_kepala'],
                'tanggal_lahir' => $data['tanggal_lahir_kepala'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin_kepala'] ?? 'L',
                'hubungan_keluarga' => 'Kepala Keluarga',
                'status_tinggal' => $data['status_tinggal'],
            ]);

            $kartuKeluarga->update(['kepala_keluarga_id' => $kepala->id]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_kk',
            'tabel_terkait' => 'kartu_keluarga',
            'record_id' => $kartuKeluarga->id,
            'detail' => ['no_kk' => $kartuKeluarga->no_kk],
        ]);

        if ($kartuKeluarga->kepala_keluarga_id) {
            return redirect()->route('admin.warga.show', $kartuKeluarga)
                ->with('success', 'Kartu keluarga beserta kepala keluarga berhasil ditambahkan.');
        }

        return redirect()->route('admin.warga.anggota.create', $kartuKeluarga)
            ->with('success', 'Kartu keluarga berhasil ditambahkan. Silakan tambahkan kepala keluarga.');
    }

    public function edit(KartuKeluarga $warga): View
    {
        return view('admin.warga.edit', ['kk' => $warga, 'kartuKeluarga' => $warga]);
    }

    public function update(UpdateKartuKeluargaRequest $request, KartuKeluarga $warga): RedirectResponse
    {
        $this->authorize('update', $warga);

        $data = $request->validated();
        unset($data['nama_kepala'], $data['nik_kepala'], $data['jenis_kelamin_kepala'], $data['tanggal_lahir_kepala']);

        $warga->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'ubah_kk',
            'tabel_terkait' => 'kartu_keluarga',
            'record_id' => $warga->id,
            'detail' => ['no_kk' => $warga->no_kk],
        ]);

        return redirect()->route('admin.warga.show', $warga)
            ->with('success', 'Data kartu keluarga berhasil diperbarui.');
    }

    public function destroy(KartuKeluarga $warga): RedirectResponse
    {
        $this->authorize('delete', $warga);

        $warga->delete(); // Soft-delete.

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_kk',
            'tabel_terkait' => 'kartu_keluarga',
            'record_id' => $warga->id,
            'detail' => ['no_kk' => $warga->no_kk],
        ]);

        return redirect()->route('admin.warga.index')
            ->with('success', 'Kartu keluarga berhasil dihapus.');
    }

    public function tambahAnggota(KartuKeluarga $kartuKeluarga): View
    {
        return view('admin.warga.anggota.create', compact('kartuKeluarga'));
    }

    public function storeAnggota(StoreWargaRequest $request, KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        $warga = Warga::create($request->validated());

        if (($request->validated('hubungan_keluarga')) === 'Kepala Keluarga' && ! $kartuKeluarga->kepala_keluarga_id) {
            $kartuKeluarga->update(['kepala_keluarga_id' => $warga->id]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_warga',
            'tabel_terkait' => 'warga',
            'record_id' => $warga->id,
            'detail' => ['nama' => $warga->nama_lengkap, 'kartu_keluarga_id' => $kartuKeluarga->id],
        ]);

        return redirect()->route('admin.warga.show', $kartuKeluarga)
            ->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public function editAnggota(Warga $anggota): View
    {
        $kartuKeluarga = $anggota->kartuKeluarga;

        return view('admin.warga.anggota.edit', compact('anggota', 'kartuKeluarga'));
    }

    public function updateAnggota(UpdateWargaRequest $request, Warga $anggota): RedirectResponse
    {
        $this->authorize('update', $anggota);

        $anggota->update($request->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'ubah_warga',
            'tabel_terkait' => 'warga',
            'record_id' => $anggota->id,
            'detail' => ['nama' => $anggota->nama_lengkap],
        ]);

        return redirect()->route('admin.warga.show', $anggota->kartu_keluarga_id)
            ->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    public function hapusAnggota(Warga $anggota): RedirectResponse
    {
        $this->authorize('delete', $anggota);

        $kartuKeluargaId = $anggota->kartu_keluarga_id;
        $anggota->delete(); // Soft-delete.

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_warga',
            'tabel_terkait' => 'warga',
            'record_id' => $anggota->id,
            'detail' => ['nama' => $anggota->nama_lengkap],
        ]);

        return redirect()->route('admin.warga.show', $kartuKeluargaId)
            ->with('success', 'Anggota keluarga berhasil dihapus.');
    }
}
