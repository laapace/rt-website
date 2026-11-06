@extends('layouts.dashboard')

@section('title', 'Kelola Agenda')
@section('deskripsi', 'Jadwalkan dan kelola kegiatan RT.')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.agenda.create') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">+ Buat Agenda</a>
    </div>

    @if (($agenda ?? collect())->count())
        <div class="space-y-4">
            @foreach ($agenda as $a)
                <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold leading-snug">{{ $a->judul }}</h2>
                            @if ($a->deskripsi)
                                <p class="mt-1 text-sm text-neutral-600">{{ $a->deskripsi }}</p>
                            @endif
                            <p class="mt-2 text-sm text-neutral-600">{{ formatAgenda($a) }}{{ $a->lokasi ? ' • ' . $a->lokasi : '' }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-4 text-sm">
                            <a href="{{ route('admin.agenda.edit', $a->id) }}" class="font-semibold text-primary-700 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.agenda.destroy', $a->id) }}" onsubmit="return confirm('Hapus agenda ini? Tindakan ini tidak bisa dibatalkan.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-danger-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        @if (method_exists($agenda, 'links'))
            <div class="mt-4">{{ $agenda->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada agenda" deskripsi="Buat agenda kegiatan agar warga tahu jadwal kerja bakti, rapat, dan acara RT lainnya." :aksiUrl="route('admin.agenda.create')" aksiTeks="Buat Agenda" />
    @endif
@endsection
