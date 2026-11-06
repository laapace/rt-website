{{-- Layout dashboard (design.md §3.2): sidebar kiri (desktop) / menu atas (HP),
     header berisi judul halaman + nama pengguna + lonceng + keluar.
     Dipakai dengan @extends('layouts.dashboard') + @section('title') + @section('content'). --}}
@php
    $pengguna = auth()->user();
    $isPengurus = $pengguna && (
        $pengguna->hasRole('pengurus') || $pengguna->hasRole('ketua') ||
        $pengguna->hasRole('sekretaris') || $pengguna->hasRole('bendahara') ||
        $pengguna->hasRole('admin')
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — Website RT 02 RW 03</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-50 font-sans text-neutral-900 antialiased">
<div class="min-h-screen lg:flex" x-data="{ menuTerbuka: false }">
    {{-- Sidebar (desktop) --}}
    <aside class="hidden w-64 shrink-0 bg-primary-700 text-white lg:block">
        <div class="sticky top-0 flex h-screen flex-col">
            <a href="{{ route('public.beranda') }}" class="flex items-center gap-2 px-5 py-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-extrabold text-primary-700">RT</span>
                <span class="text-lg font-bold">RT 02 RW 03</span>
            </a>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4 text-sm font-medium" aria-label="Menu dashboard">
                <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-primary-100">Warga</p>
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard') ? 'bg-primary-600 font-bold' : '' }}">Dashboard</a>
                <a href="{{ route('dashboard.iuran.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.iuran.*') ? 'bg-primary-600 font-bold' : '' }}">Tagihan Saya</a>
                <a href="{{ route('dashboard.surat.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.surat.*') ? 'bg-primary-600 font-bold' : '' }}">Surat</a>
                <a href="{{ route('dashboard.laporan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.laporan.*') ? 'bg-primary-600 font-bold' : '' }}">Laporan</a>
                <a href="{{ route('dashboard.ronda.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.ronda.*') ? 'bg-primary-600 font-bold' : '' }}">Jadwal Ronda</a>
                <a href="{{ route('dashboard.keluarga.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.keluarga.*') ? 'bg-primary-600 font-bold' : '' }}">Data Keluarga</a>
                <a href="{{ route('dashboard.notifikasi.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('dashboard.notifikasi.*') ? 'bg-primary-600 font-bold' : '' }}">Notifikasi</a>

                @if ($isPengurus)
                    <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-primary-100">Pengurus</p>
                    <a href="{{ route('admin.warga.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.warga.*') ? 'bg-primary-600 font-bold' : '' }}">Data Warga</a>
                    <a href="{{ route('admin.pengumuman.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.pengumuman.*') ? 'bg-primary-600 font-bold' : '' }}">Pengumuman</a>
                    <a href="{{ route('admin.agenda.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.agenda.*') ? 'bg-primary-600 font-bold' : '' }}">Agenda</a>
                    <a href="{{ route('admin.iuran.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.iuran.*') ? 'bg-primary-600 font-bold' : '' }}">Iuran</a>
                    <a href="{{ route('admin.kas.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.kas.*') ? 'bg-primary-600 font-bold' : '' }}">Kas</a>
                    <a href="{{ route('admin.surat.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.surat.*') ? 'bg-primary-600 font-bold' : '' }}">Surat</a>
                    <a href="{{ route('admin.laporan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.laporan.*') ? 'bg-primary-600 font-bold' : '' }}">Laporan</a>
                    <a href="{{ route('admin.ronda.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.ronda.*') ? 'bg-primary-600 font-bold' : '' }}">Ronda</a>
                    <a href="{{ route('admin.pengguna.index') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600 {{ request()->routeIs('admin.pengguna.*') ? 'bg-primary-600 font-bold' : '' }}">Pengguna</a>
                @endif

                <a href="{{ route('public.beranda') }}" class="block rounded-lg px-3 py-2 hover:bg-primary-600">← Lihat Website Publik</a>
            </nav>

            <div class="border-t border-primary-600 p-4 text-sm">
                <p class="font-semibold">{{ $pengguna->name ?? 'Pengguna' }}</p>
                <p class="truncate text-xs text-primary-100">{{ $pengguna->email ?? '' }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-white px-3 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-100">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Kolom utama --}}
    <div class="min-w-0 flex-1">
        {{-- Header --}}
        <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" @click="menuTerbuka = ! menuTerbuka" class="rounded-md p-2 hover:bg-neutral-100 lg:hidden" aria-label="Buka/tutup menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold leading-tight sm:text-xl">@yield('title', 'Dashboard')</h1>
                        @hasSection('deskripsi')
                            <p class="hidden text-sm text-neutral-600 sm:block">@yield('deskripsi')</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.notifikasi.index') }}" class="rounded-full p-2 hover:bg-neutral-100" aria-label="Notifikasi">
                        <svg class="h-6 w-6 text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </a>
                    <span class="hidden items-center gap-2 rounded-full bg-primary-100 px-3 py-1.5 text-sm font-semibold text-primary-700 sm:inline-flex">{{ $pengguna->name ?? 'Pengguna' }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Keluar</button>
                    </form>
                </div>
            </div>

            {{-- Menu HP (drawer) --}}
            <nav x-show="menuTerbuka" class="border-t border-neutral-200 px-4 py-3 lg:hidden" aria-label="Menu dashboard (HP)">
                <div class="grid grid-cols-2 gap-1 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Dashboard</a>
                    <a href="{{ route('dashboard.iuran.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Tagihan Saya</a>
                    <a href="{{ route('dashboard.surat.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Surat</a>
                    <a href="{{ route('dashboard.laporan.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Laporan</a>
                    <a href="{{ route('dashboard.ronda.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Jadwal Ronda</a>
                    <a href="{{ route('dashboard.keluarga.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Data Keluarga</a>
                    <a href="{{ route('dashboard.notifikasi.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Notifikasi</a>
                    @if ($isPengurus)
                        <a href="{{ route('admin.warga.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Data Warga</a>
                        <a href="{{ route('admin.pengumuman.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Pengumuman</a>
                        <a href="{{ route('admin.agenda.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Agenda</a>
                        <a href="{{ route('admin.iuran.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Iuran</a>
                        <a href="{{ route('admin.kas.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Kas</a>
                        <a href="{{ route('admin.surat.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Surat</a>
                        <a href="{{ route('admin.laporan.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Laporan</a>
                        <a href="{{ route('admin.ronda.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Ronda</a>
                        <a href="{{ route('admin.pengguna.index') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Pengguna</a>
                    @endif
                    <a href="{{ route('public.beranda') }}" class="rounded-lg px-3 py-2 hover:bg-neutral-100">Website Publik</a>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-primary-700 px-3 py-2 text-sm font-semibold text-white">Keluar</button>
                </form>
            </nav>
        </header>

        {{-- Konten --}}
        <main class="mx-auto w-full max-w-[1280px] px-4 py-6 sm:px-6 lg:px-8">
            <x-alert />
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
