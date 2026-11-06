@extends('layouts.dashboard')

@section('title', 'Jadwal Ronda')
@section('deskripsi', 'Jadwal ronda malam RT dan daftar petugasnya.')

@section('content')
    {{-- Giliran saya --}}
    @if (($jadwalSaya ?? collect())->count())
        <h2 class="mb-4 text-xl font-bold">Giliran Saya</h2>
        <div class="mb-8 space-y-4">
            @foreach ($jadwalSaya as $j)
                <div class="rounded-xl border border-primary-200 bg-primary-50 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-lg font-bold">{{ formatTanggal($j->tanggal) }}</p>
                            <p class="text-sm text-neutral-600">{{ $j->blok }} • 22.00–04.00 WIB</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full border border-primary-200 bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700">Giliran Anda</span>
                    </div>
                    <div class="mt-3 border-t border-primary-200 pt-3">
                        <p class="text-sm font-semibold">Petugas:</p>
                        @if (($j->petugas ?? collect())->count())
                            <ul class="mt-1 list-inside list-disc text-sm text-neutral-600">
                                @foreach ($j->petugas as $petugas)
                                    <li>{{ is_string($petugas) ? $petugas : ($petugas->nama ?? '—') }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1 text-sm text-neutral-600">—</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Semua jadwal mendatang --}}
    <h2 class="mb-4 text-xl font-bold">Jadwal Ronda Mendatang</h2>
    @if (($jadwalMendatang ?? collect())->count())
        <div class="space-y-4">
            @foreach ($jadwalMendatang as $j)
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-lg font-bold">{{ formatTanggal($j->tanggal) }}</p>
                            <p class="text-sm text-neutral-600">{{ $j->blok }} • 22.00–04.00 WIB</p>
                        </div>
                        @if (($jadwalSaya ?? collect())->contains('id', $j->id))
                            <span class="inline-flex w-fit rounded-full border border-primary-200 bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700">Giliran Anda</span>
                        @endif
                    </div>
                    <div class="mt-3 border-t border-neutral-200 pt-3">
                        <p class="text-sm font-semibold">Petugas:</p>
                        @if (($j->petugas ?? collect())->count())
                            <ul class="mt-1 list-inside list-disc text-sm text-neutral-600">
                                @foreach ($j->petugas as $petugas)
                                    <li>{{ is_string($petugas) ? $petugas : ($petugas->nama ?? '—') }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1 text-sm text-neutral-600">—</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if (method_exists($jadwalMendatang, 'links'))
            <div class="mt-4">{{ $jadwalMendatang->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada jadwal ronda" deskripsi="Jadwal ronda bulan ini belum dibuat pengurus. Silakan periksa lagi nanti." />
    @endif
@endsection
