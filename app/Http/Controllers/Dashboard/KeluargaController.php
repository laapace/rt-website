<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\KartuKeluarga;
use Illuminate\View\View;

class KeluargaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $kartuKeluarga = $user->kartu_keluarga_id
            ? KartuKeluarga::with('warga')->find($user->kartu_keluarga_id)
            : null;

        // Blade memakai $kk / $anggota dan $warga->nik_tersamar, bukan kolom nik mentah.
        $kk = $kartuKeluarga;
        $anggota = $kartuKeluarga?->warga ?? collect();

        return view('dashboard.keluarga.index', compact('kk', 'anggota', 'kartuKeluarga'));
    }
}
