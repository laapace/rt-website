@extends('layouts.dashboard')

@section('title', 'Laporan Saya')
@section('deskripsi', 'Laporkan masalah lingkungan dan lacak tindak lanjutnya.')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('dashboard.laporan.create') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">+ Buat Laporan</a>
    </div>

    @if (($laporan ?? collect())->count())
        <div class="space-y-4">
            @foreach ($laporan as $l)
                <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ labelKategoriLaporan($l->kategori) }}</span>
                            <span class="text-neutral-600">{{ formatTanggal($l->created_at) }}</span>
                        </div>
                        <x-badge :status="$l->status" />
                    </div>
                    <h2 class="mt-2 text-lg font-semibold leading-snug">{{ $l->judul }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-neutral-600">{{ $l->deskripsi }}</p>
                    @if ($l->foto_path ?? null)
                        <img src="{{ asset('storage/' . $l->foto_path) }}" alt="Foto laporan: {{ $l->judul }}" class="mt-3 max-h-64 rounded-lg object-cover">
                    @endif
                    @if ($l->komentar_pengurus ?? null)
                        <p class="mt-3 rounded-lg bg-neutral-50 px-3 py-2 text-sm"><span class="font-semibold">Komentar pengurus: </span>{{ $l->komentar_pengurus }}</p>
                    @endif
                </article>
            @endforeach
        </div>
        @if (method_exists($laporan, 'links'))
            <div class="mt-4">{{ $laporan->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada laporan" deskripsi="Laporkan lampu jalan mati, sampah menumpuk, atau masalah keamanan — pengurus akan menindaklanjuti." :aksiUrl="route('dashboard.laporan.create')" aksiTeks="Buat Laporan" />
    @endif
@endsection
