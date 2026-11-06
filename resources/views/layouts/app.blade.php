{{-- Layout publik (design.md §3.1): navbar hijau tua sticky, hero opsional, footer.
     Tetap layout komponen Breeze (<x-app-layout>) agar halaman profile/auth bawaan tidak rusak. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Website RT 02 RW 03') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-50 font-sans text-neutral-900 antialiased">
<div class="flex min-h-screen flex-col" x-data="{ menuTerbuka: false }">
    {{-- Navbar --}}
    <header class="sticky top-0 z-40 bg-primary-700 text-white shadow">
        <div class="mx-auto flex h-16 max-w-[1120px] items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('public.beranda') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-extrabold text-primary-700">RT</span>
                <span class="text-lg font-bold leading-tight">RT 02 RW 03</span>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium md:flex" aria-label="Menu utama">
                <a href="{{ route('public.beranda') }}" class="hover:underline {{ request()->routeIs('public.beranda') ? 'font-bold underline' : '' }}">Beranda</a>
                <a href="{{ route('public.profil') }}" class="hover:underline {{ request()->routeIs('public.profil') ? 'font-bold underline' : '' }}">Profil</a>
                <a href="{{ route('public.pengumuman.index') }}" class="hover:underline {{ request()->routeIs('public.pengumuman.*') ? 'font-bold underline' : '' }}">Pengumuman</a>
                <a href="{{ route('public.agenda.index') }}" class="hover:underline {{ request()->routeIs('public.agenda.*') ? 'font-bold underline' : '' }}">Agenda</a>
                <a href="{{ route('public.kontak') }}" class="hover:underline {{ request()->routeIs('public.kontak') ? 'font-bold underline' : '' }}">Kontak</a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-100">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-100">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-lg border border-white px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Daftar</a>
                @endauth
            </div>

            <button type="button" @click="menuTerbuka = ! menuTerbuka" class="rounded-md p-2 hover:bg-primary-600 md:hidden" aria-label="Buka/tutup menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        {{-- Menu HP --}}
        <nav x-show="menuTerbuka" class="border-t border-primary-600 px-4 py-3 md:hidden" aria-label="Menu utama (HP)">
            <div class="flex flex-col gap-1 text-sm font-medium">
                <a href="{{ route('public.beranda') }}" class="rounded-md px-2 py-2 hover:bg-primary-600">Beranda</a>
                <a href="{{ route('public.profil') }}" class="rounded-md px-2 py-2 hover:bg-primary-600">Profil</a>
                <a href="{{ route('public.pengumuman.index') }}" class="rounded-md px-2 py-2 hover:bg-primary-600">Pengumuman</a>
                <a href="{{ route('public.agenda.index') }}" class="rounded-md px-2 py-2 hover:bg-primary-600">Agenda</a>
                <a href="{{ route('public.kontak') }}" class="rounded-md px-2 py-2 hover:bg-primary-600">Kontak</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mt-1 rounded-lg bg-white px-4 py-2 text-center font-semibold text-primary-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="mt-1 rounded-lg bg-white px-4 py-2 text-center font-semibold text-primary-700">Masuk</a>
                    <a href="{{ route('register') }}" class="mt-1 rounded-lg border border-white px-4 py-2 text-center font-semibold">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- Hero opsional (hanya beranda) --}}
    @isset($hero)
        {{ $hero }}
    @endisset

    {{-- Judul halaman bawaan Breeze (dipakai halaman profile) --}}
    @isset($header)
        <header class="bg-white shadow">
            <div class="mx-auto max-w-[1120px] px-4 py-6 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{-- Konten --}}
    <main class="mx-auto w-full max-w-[1120px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
        <x-alert />
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-primary-700 text-white">
        <div class="mx-auto grid max-w-[1120px] gap-6 px-4 py-8 sm:grid-cols-3 sm:px-6 lg:px-8">
            <div>
                <p class="text-lg font-bold">RT 02 RW 03</p>
                <p class="mt-2 text-sm text-primary-100">{{ ($pengaturan->alamat ?? null) ? $pengaturan->alamat . ', ' : '' }}{{ $pengaturan->kelurahan ?? 'Kelurahan' }} — {{ $pengaturan->kecamatan ?? 'Kecamatan' }}, {{ $pengaturan->kota ?? 'Kota' }}</p>
            </div>
            <div>
                <p class="font-semibold">Hubungi Pengurus</p>
                <p class="mt-2 text-sm text-primary-100">Telepon/WA: {{ $pengaturan->telepon ?? '—' }}</p>
                <p class="mt-1 text-sm text-primary-100">Ketua RT: {{ $pengaturan->nama_ketua ?? '—' }}</p>
            </div>
            <div>
                <p class="font-semibold">Jam Layanan</p>
                <p class="mt-2 text-sm text-primary-100">Senin–Jumat, 19.00–21.00 WIB</p>
                <p class="mt-1 text-sm text-primary-100">Sabtu–Minggu, 09.00–12.00 WIB</p>
            </div>
        </div>
        <div class="border-t border-primary-600 py-4 text-center text-xs text-primary-100">
            &copy; {{ date('Y') }} RT 02 RW 03 — Website RT
        </div>
    </footer>
</div>
</body>
</html>
