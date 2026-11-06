<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\PengaturanRt;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function index(): View
    {
        $pengumuman = Pengumuman::where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(3)
            ->get();

        $agenda = Agenda::where('tanggal_mulai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->take(4)
            ->get();

        $pengaturan = PengaturanRt::first();

        return view('public.beranda', compact('pengumuman', 'agenda', 'pengaturan'));
    }

    public function profil(): View
    {
        $pengaturan = PengaturanRt::first();

        $pengurus = User::whereHas('roles', function ($query) {
            $query->whereIn('peran', ['ketua', 'sekretaris', 'bendahara']);
        })->with('roles')->get();

        return view('public.profil', compact('pengaturan', 'pengurus'));
    }

    public function kontak(): View
    {
        $pengaturan = PengaturanRt::first();

        return view('public.kontak', compact('pengaturan'));
    }
}
