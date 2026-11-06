<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())->latest()->paginate(15);

        return view('dashboard.notifikasi.index', compact('notifikasi'));
    }

    public function bacaSemua(): RedirectResponse
    {
        Notifikasi::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi sudah ditandai telah dibaca.');
    }

    public function tandaiBaca(Notifikasi $notifikasi): RedirectResponse
    {
        abort_if($notifikasi->user_id !== auth()->id(), 403);

        $notifikasi->update(['is_read' => true]);

        return back();
    }
}
