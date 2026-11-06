@extends('layouts.dashboard')

@section('title', 'Kelola Pengumuman')
@section('deskripsi', 'Buat, edit, sematkan, dan hapus pengumuman RT.')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.pengumuman.create') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">+ Buat Pengumuman</a>
    </div>

    @if (($pengumuman ?? collect())->count())
        <div class="space-y-4">
            @foreach ($pengumuman as $p)
                <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ $p->kategori ?? 'Umum' }}</span>
                        @if ($p->is_penting ?? false)
                            <x-badge status="penting" />
                        @endif
                        <span class="text-neutral-600">{{ formatTanggal($p->created_at) }}</span>
                    </div>
                    <h2 class="mt-2 text-lg font-semibold leading-snug">{{ $p->judul }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-neutral-600">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 180) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                        <a href="{{ route('public.pengumuman.show', $p->id) }}" target="_blank" rel="noopener" class="font-semibold text-primary-700 hover:underline">Lihat</a>
                        <a href="{{ route('admin.pengumuman.edit', $p->id) }}" class="font-semibold text-primary-700 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.pengumuman.destroy', $p->id) }}" onsubmit="return confirm('Hapus pengumuman ini? Tindakan ini tidak bisa dibatalkan.')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-danger-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        @if (method_exists($pengumuman, 'links'))
            <div class="mt-4">{{ $pengumuman->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada pengumuman" deskripsi="Buat pengumuman pertama agar warga selalu mendapat kabar terbaru." :aksiUrl="route('admin.pengumuman.create')" aksiTeks="Buat Pengumuman" />
    @endif
@endsection
