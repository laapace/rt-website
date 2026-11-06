<x-app-layout>
    <a href="{{ route('public.pengumuman.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Semua Pengumuman</a>

    <article class="mt-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:p-8">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ ucfirst($pengumuman->kategori ?? 'Umum') }}</span>
            @if ($pengumuman->is_pinned ?? false)
                <x-badge status="penting" />
            @endif
            <span class="text-neutral-600">{{ formatTanggal($pengumuman->created_at) }}</span>
        </div>

        <h1 class="mt-3 text-2xl font-bold leading-tight sm:text-3xl">{{ $pengumuman->judul }}</h1>
        <p class="mt-2 text-sm text-neutral-600">Ditulis oleh {{ $pengumuman->dibuatOleh?->name ?? 'Pengurus RT' }}</p>

        @if ($pengumuman->gambar_path ?? null)
            <img src="{{ asset('storage/' . $pengumuman->gambar_path) }}" alt="Lampiran pengumuman: {{ $pengumuman->judul }}" class="mt-5 max-h-96 w-full rounded-lg object-cover">
        @endif

        <div class="mt-5 space-y-4 text-base leading-relaxed text-neutral-900">
            {!! nl2br(e($pengumuman->isi)) !!}
        </div>
    </article>
</x-app-layout>
