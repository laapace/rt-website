<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use Illuminate\View\View;

class RondaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $jadwalMendatang = JadwalRonda::where('tanggal', '>=', now()->toDateString())
            ->with('petugas')
            ->orderBy('tanggal')
            ->paginate(10);

        $jadwalSaya = JadwalRonda::where('tanggal', '>=', now()->toDateString())
            ->when($user->warga_id, function ($query) use ($user) {
                $query->whereHas('petugas', function ($sub) use ($user) {
                    $sub->where('warga.id', $user->warga_id);
                });
            })
            ->orderBy('tanggal')
            ->get();

        return view('dashboard.ronda.index', compact('jadwalMendatang', 'jadwalSaya'));
    }
}
