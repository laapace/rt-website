<x-app-layout>
    <h1 class="text-2xl font-bold sm:text-3xl">Pengumuman</h1>
    <p class="mt-1 text-sm text-neutral-600">Kabar terbaru dari pengurus RT — pengumuman penting selalu tampil di atas.</p>

    {{-- Filter kategori + cari --}}
    <form method="GET" action="{{ route('public.pengumuman.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
        <label class="sr-only" for="kategori">Kategori</label>
        <select id="kategori" name="kategori" class="rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600 sm:w-48">
            <option value="">Semua kategori</option>
            @foreach (['umum' => 'Umum', 'keuangan' => 'Keuangan', 'kegiatan' => 'Kegiatan', 'keamanan' => 'Keamanan'] as $nilai => $label)
                <option value="{{ $nilai }}" {{ ($kategori ?? request('kategori')) === $nilai ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="q">Cari pengumuman</label>
        <input id="q" type="search" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari judul pengumuman…" class="flex-1 rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
        <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Cari</button>
    </form>

    <div class="mt-6 space-y-4">
        @forelse ($pengumuman as $p)
            <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ ucfirst($p->kategori ?? 'Umum') }}</span>
                    @if ($p->is_pinned ?? false)
                        <x-badge status="penting" />
                    @endif
                    <span class="text-neutral-600">{{ formatTanggal($p->created_at) }}</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold leading-snug">
                    <a href="{{ route('public.pengumuman.show', $p->id) }}" class="hover:text-primary-700 hover:underline">{{ $p->judul }}</a>
                </h2>
                <p class="mt-1 line-clamp-2 text-sm text-neutral-600">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 200) }}</p>
                <a href="{{ route('public.pengumuman.show', $p->id) }}" class="mt-3 inline-block text-sm font-semibold text-primary-700 hover:underline">Baca selengkapnya →</a>
            </article>
        @empty
            <x-empty-state judul="Tidak ada pengumuman" deskripsi="Belum ada pengumuman untuk kategori atau pencarian ini. Coba kata kunci lain." />
        @endforelse
    </div>

    <div class="mt-6">
        {{ $pengumuman->links() }}
    </div>
</x-app-layout>
