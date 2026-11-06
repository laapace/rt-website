<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaRequest;
use App\Models\ActivityLog;
use App\Models\Agenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        $agenda = Agenda::orderBy('tanggal_mulai')->paginate(15);

        return view('admin.agenda.index', compact('agenda'));
    }

    public function create(): View
    {
        return view('admin.agenda.create');
    }

    public function store(StoreAgendaRequest $request): RedirectResponse
    {
        $agenda = Agenda::create(array_merge($request->validated(), [
            'dibuat_oleh' => auth()->id(),
        ]));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_agenda',
            'tabel_terkait' => 'agenda',
            'record_id' => $agenda->id,
            'detail' => ['judul' => $agenda->judul],
        ]);

        return redirect()->route('admin.agenda.index')
            ->with('success', 'Agenda berhasil dibuat.');
    }

    public function edit(Agenda $agenda): View
    {
        return view('admin.agenda.edit', compact('agenda'));
    }

    public function update(StoreAgendaRequest $request, Agenda $agenda): RedirectResponse
    {
        $this->authorize('update', $agenda);

        $agenda->update($request->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'ubah_agenda',
            'tabel_terkait' => 'agenda',
            'record_id' => $agenda->id,
            'detail' => ['judul' => $agenda->judul],
        ]);

        return redirect()->route('admin.agenda.index')
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $this->authorize('delete', $agenda);

        $agenda->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_agenda',
            'tabel_terkait' => 'agenda',
            'record_id' => $agenda->id,
            'detail' => ['judul' => $agenda->judul],
        ]);

        return redirect()->route('admin.agenda.index')
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
