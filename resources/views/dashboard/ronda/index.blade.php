@extends('layouts.dashboard')

@section('title', 'Jadwal Ronda')
@section('deskripsi', 'Jadwal ronda malam RT dan daftar petugasnya.')

@section('content')
    @if (($jadwal ?? collect())->count())
        <div class="space-y-4">
            @foreach ($jadwal as $j)
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-lg font-bold">{{ formatTanggal($j->tanggal) }}</p>
                            <p class="text-sm text-neutral-600">Blok {{ $j->blok }}{{ $j->waktu ? ' • ' . $j->waktu : ' • 22.00–04.00 WIB' }}</p>
                        </div>
                        @if (($j->is_saya ?? false) || (auth()->user()->kartu_keluarga_id ?? null) === ($j->kartu_keluarga_id ?? null))
                            <span class="inline-flex w-fit rounded-full border border-primary-200 bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700">Giliran Anda</span>
                        @endif
                    </div>
                    <div class="mt-3 border-t border-neutral-200 pt-3">
                        <p class="text-sm font-semibold">Petugas:</p>
                        @if (is_iterable($j->petugas ?? null))
                            <ul class="mt-1 list-inside list-disc text-sm text-neutral-600">
                                @foreach ($j->petugas as $petugas)
                                    <li>{{ is_string($petugas) ? $petugas : ($petugas->nama ?? $petugas->warga->nama ?? '—') }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1 text-sm text-neutral-600">{{ $j->petugas ?? '—' }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if (method_exists($jadwal, 'links'))
            <div class="mt-4">{{ $jadwal->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada jadwal ronda" deskripsi="Jadwal ronda bulan ini belum dibuat pengurus. Silakan periksa lagi nanti." />
    @endif
@endsection
