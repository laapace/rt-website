<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        $agenda = Agenda::where('tanggal_mulai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->paginate(10);

        return view('public.agenda.index', compact('agenda'));
    }
}
