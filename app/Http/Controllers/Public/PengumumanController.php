<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $pengumuman = Pengumuman::where('is_published', true)
            ->when($request->filled('kategori'), function ($query) use ($request) {
                $query->where('kategori', $request->string('kategori'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($q) {
                    $sub->where('judul', 'like', $q)->orWhere('isi', 'like', $q);
                });
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('public.pengumuman.index', compact('pengumuman'));
    }

    public function show(Pengumuman $pengumuman): View
    {
        if (! $pengumuman->is_published) {
            abort(404);
        }

        return view('public.pengumuman.show', compact('pengumuman'));
    }
}
