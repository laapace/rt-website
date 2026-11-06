<x-app-layout>
    <x-slot name="hero">
        <section class="bg-primary-700 text-white">
            <div class="mx-auto max-w-[1120px] px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <h1 class="text-2xl font-bold leading-tight sm:text-4xl">Selamat datang di {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}</h1>
                <p class="mt-3 max-w-2xl text-base text-primary-100 sm:text-lg">Layanan warga dalam satu tempat: bayar iuran, ajukan surat, laporkan masalah lingkungan, dan ikuti kabar RT terbaru.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('public.pengumuman.index') }}" class="rounded-lg bg-white px-5 py-3 text-center text-sm font-semibold text-primary-700 hover:bg-primary-100">Lihat Pengumuman</a>
                    <a href="{{ route('dashboard.surat.index') }}" class="rounded-lg border border-white px-5 py-3 text-center text-sm font-semibold text-white hover:bg-primary-600">Ajukan Surat — perlu login</a>
                </div>
            </div>
        </section>
    </x-slot>

    {{-- Pengumuman terbaru + agenda --}}
    <section class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold">Pengumuman Terbaru</h2>
                <a href="{{ route('public.pengumuman.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">Lihat semua →</a>
            </div>

            @forelse ($pengumuman ?? [] as $p)
                <article class="mb-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ $p->kategori ?? 'Umum' }}</span>
                        @if ($p->is_penting ?? false)
                            <x-badge status="penting" />
                        @endif
                        <span class="text-neutral-600">{{ formatTanggal($p->created_at) }}</span>
                    </div>
                    <h3 class="mt-2 text-lg font-semibold leading-snug">
                        <a href="{{ route('public.pengumuman.show', $p->id) }}" class="hover:text-primary-700 hover:underline">{{ $p->judul }}</a>
                    </h3>
                    <p class="mt-1 line-clamp-2 text-sm text-neutral-600">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 160) }}</p>
                </article>
            @empty
                <x-empty-state judul="Belum ada pengumuman" deskripsi="Pengumuman terbaru dari pengurus akan tampil di sini." />
            @endforelse
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold">Agenda Mendatang</h2>
                <a href="{{ route('public.agenda.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">Lihat semua →</a>
            </div>

            @forelse (($agenda ?? collect())->take(4) as $a)
                <div class="mb-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <p class="text-sm font-semibold text-primary-700">{{ formatTanggal($a->tanggal) }}{{ $a->waktu ? ' • ' . $a->waktu : '' }}</p>
                    <p class="mt-1 font-semibold leading-snug">{{ $a->judul }}</p>
                    @if ($a->lokasi)
                        <p class="mt-1 text-sm text-neutral-600">{{ $a->lokasi }}</p>
                    @endif
                </div>
            @empty
                <x-empty-state judul="Belum ada agenda" deskripsi="Agenda kegiatan RT akan tampil di sini." />
            @endforelse
        </div>
    </section>

    {{-- Layanan warga --}}
    <section class="mt-10">
        <h2 class="mb-4 text-xl font-bold">Layanan Warga</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('dashboard.iuran.index') }}" class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:shadow-md">
                <p class="text-lg font-semibold">Iuran</p>
                <p class="mt-1 text-sm text-neutral-600">Lihat tagihan bulanan dan unggah bukti bayar.</p>
                <p class="mt-3 text-sm font-semibold text-primary-700">Bayar iuran →</p>
            </a>
            <a href="{{ route('dashboard.surat.index') }}" class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:shadow-md">
                <p class="text-lg font-semibold">Surat</p>
                <p class="mt-1 text-sm text-neutral-600">Ajukan surat domisili dan keterangan lainnya secara online.</p>
                <p class="mt-3 text-sm font-semibold text-primary-700">Ajukan surat →</p>
            </a>
            <a href="{{ route('dashboard.laporan.index') }}" class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:shadow-md">
                <p class="text-lg font-semibold">Laporan</p>
                <p class="mt-1 text-sm text-neutral-600">Laporkan lampu mati, sampah, atau masalah keamanan.</p>
                <p class="mt-3 text-sm font-semibold text-primary-700">Buat laporan →</p>
            </a>
            <a href="{{ route('dashboard.ronda.index') }}" class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:shadow-md">
                <p class="text-lg font-semibold">Ronda</p>
                <p class="mt-1 text-sm text-neutral-600">Lihat jadwal ronda malam dan daftar petugas.</p>
                <p class="mt-3 text-sm font-semibold text-primary-700">Lihat jadwal →</p>
            </a>
        </div>
    </section>

    {{-- Profil singkat --}}
    <section class="mt-10 rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:p-6">
        <h2 class="text-xl font-bold">Tentang {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}</h2>
        <p class="mt-2 text-sm leading-relaxed text-neutral-600 sm:text-base">{{ \Illuminate\Support\Str::limit($pengaturan->sejarah ?? 'RT 02 RW 03 adalah rukun tetangga yang melayani warga dengan semangat gotong royong — pengumuman, iuran, surat, dan laporan warga kini bisa diurus secara online.', 300) }}</p>
        <a href="{{ route('public.profil') }}" class="mt-3 inline-block text-sm font-semibold text-primary-700 hover:underline">Baca profil lengkap →</a>
    </section>
</x-app-layout>
