<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        $users = User::with(['roles', 'kartuKeluarga'])->orderBy('name')->paginate(15);

        return view('admin.pengguna.index', compact('users'));
    }

    public function aktifkan(User $pengguna): RedirectResponse
    {
        $pengguna->update(['is_active' => true]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'aktifkan_akun',
            'tabel_terkait' => 'users',
            'record_id' => $pengguna->id,
            'detail' => ['nama' => $pengguna->name, 'email' => $pengguna->email],
        ]);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Akun '.$pengguna->name.' berhasil diaktifkan.');
    }

    public function tambahPeran(Request $request, User $pengguna): RedirectResponse
    {
        $request->validate([
            'peran' => ['required', 'in:warga,ketua,sekretaris,bendahara,admin'],
        ], [
            'peran.required' => 'Peran wajib dipilih.',
            'peran.in' => 'Peran tidak valid.',
        ]);

        $pengguna->roles()->firstOrCreate(['peran' => $request->string('peran')]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'tambah_peran',
            'tabel_terkait' => 'user_roles',
            'record_id' => $pengguna->id,
            'detail' => ['peran' => $request->string('peran')],
        ]);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Peran berhasil ditambahkan.');
    }

    public function hapusPeran(Request $request, User $pengguna): RedirectResponse
    {
        $request->validate([
            'peran' => ['required', 'in:warga,ketua,sekretaris,bendahara,admin'],
        ], [
            'peran.required' => 'Peran wajib dipilih.',
            'peran.in' => 'Peran tidak valid.',
        ]);

        $peran = $request->string('peran')->toString();

        if ($pengguna->id === auth()->id() && $peran === 'admin') {
            return redirect()->route('admin.pengguna.index')
                ->with('error', 'Kamu tidak dapat menghapus peran admin dari akunmu sendiri.');
        }

        $pengguna->roles()->where('peran', $peran)->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi' => 'hapus_peran',
            'tabel_terkait' => 'user_roles',
            'record_id' => $pengguna->id,
            'detail' => ['peran' => $peran],
        ]);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Peran berhasil dihapus.');
    }
}
