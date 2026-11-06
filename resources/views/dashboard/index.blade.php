@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('deskripsi', 'Ringkasan layanan RT Anda hari ini.')

@section('content')
    @php
        $jam = (int) date('H');
        $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 19 ? 'Selamat sore' : 'Selamat malam'));
    @endphp

    <h2 class="text-xl font-bold sm:text-2xl">{{ $sapaan }}, Bapak/Ibu {{ auth()->user()->name }}</h2>
    <p class="mt-1 text-sm text-neutral-600">Berikut ringkasan tagihan, surat, dan laporan Anda.</p>

    {{-- 4 kartu ringkasan --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Tagihan Bulan Ini" :nilai="isset($stats['tagihan_bulan_ini']) ? formatRupiah((int) $stats['tagihan_bulan_ini']) : '—'" :sub="$stats['tagihan_status'] ?? 'Belum ada tagihan bulan ini'" />
        <x-stat-card label="Surat Terakhir" :nilai="$stats['surat_terakhir'] ?? '—'" :sub="$stats['surat_status'] ?? 'Belum pernah mengajukan surat'" />
        <x-stat-card label="Laporan Saya (Terbuka)" :nilai="$stats['laporan_terbuka'] ?? 0" sub="Laporan berstatus Baru / Diproses" />
        <x-stat-card label="Ronda Berikutnya" :nilai="isset($stats['ronda_berikutnya']) ? formatTanggal($stats['ronda_berikutnya']) : '—'" :sub="$stats['ronda_keterangan'] ?? 'Belum ada jadwal ronda'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Pengumuman terbaru --}}
        <x-card>
            <x-slot name="judul">Pengumuman Terbaru</x-slot>
            @forelse ($pengumuman ?? [] as $p)
                <div class="border-b border-neutral-200 py-3 first:pt-0 last:border-0 last:pb-0">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ $p->kategori ?? 'Umum' }}</span>
                        <span class="text-neutral-600">{{ formatTanggal($p->created_at) }}</span>
                    </div>
                    <a href="{{ route('public.pengumuman.show', $p->id) }}" class="mt-1 block font-semibold leading-snug hover:text-primary-700 hover:underline">{{ $p->judul }}</a>
                </div>
            @empty
                <p class="text-sm text-neutral-600">Belum ada pengumuman.</p>
            @endforelse
            <a href="{{ route('public.pengumuman.index') }}" class="mt-4 inline-block text-sm font-semibold text-primary-700 hover:underline">Lihat semua pengumuman →</a>
        </x-card>

        {{-- Notifikasi --}}
        <x-card>
            <x-slot name="judul">Notifikasi</x-slot>
            @forelse (($notifikasi ?? collect())->take(5) as $n)
                <div class="border-b border-neutral-200 py-3 first:pt-0 last:border-0 last:pb-0">
                    <p class="text-sm font-semibold leading-snug">{{ $n->judul ?? $n->pesan }}</p>
                    @if (($n->judul ?? null) && ($n->pesan ?? null))
                        <p class="mt-0.5 text-sm text-neutral-600">{{ $n->pesan }}</p>
                    @endif
                    <p class="mt-1 text-xs text-neutral-600">{{ formatTanggal($n->created_at) }}</p>
                </div>
            @empty
                <p class="text-sm text-neutral-600">Tidak ada notifikasi baru.</p>
            @endforelse
            <a href="{{ route('dashboard.notifikasi.index') }}" class="mt-4 inline-block text-sm font-semibold text-primary-700 hover:underline">Lihat semua notifikasi →</a>
        </x-card>
    </div>

    {{-- Aksi cepat --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('dashboard.iuran.index') }}" class="rounded-xl bg-primary-700 px-5 py-4 text-center text-sm font-semibold text-white hover:bg-primary-600">Lihat Tagihan Saya</a>
        <a href="{{ route('dashboard.surat.create') }}" class="rounded-xl border border-primary-700 bg-white px-5 py-4 text-center text-sm font-semibold text-primary-700 hover:bg-primary-100">Ajukan Surat</a>
        <a href="{{ route('dashboard.laporan.create') }}" class="rounded-xl border border-primary-700 bg-white px-5 py-4 text-center text-sm font-semibold text-primary-700 hover:bg-primary-100">Buat Laporan</a>
    </div>
@endsection
