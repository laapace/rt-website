<x-app-layout>
    <h1 class="text-2xl font-bold sm:text-3xl">Agenda &amp; Kegiatan</h1>
    <p class="mt-1 text-sm text-neutral-600">Kegiatan RT yang akan datang — kerja bakti, ronda, rapat warga, dan lainnya.</p>

    <div class="mt-6 space-y-4">
        @forelse ($agenda as $a)
            <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold leading-snug">{{ $a->judul }}</h2>
                        @if ($a->deskripsi)
                            <p class="mt-1 text-sm leading-relaxed text-neutral-600">{{ $a->deskripsi }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 rounded-lg bg-primary-100 px-4 py-2 text-center">
                        <p class="text-sm font-bold text-primary-700">{{ formatTanggal($a->tanggal) }}</p>
                        @if ($a->waktu)
                            <p class="text-xs text-primary-700">{{ $a->waktu }}</p>
                        @endif
                    </div>
                </div>
                @if ($a->lokasi)
                    <p class="mt-3 text-sm text-neutral-600">Lokasi: {{ $a->lokasi }}</p>
                @endif
            </article>
        @empty
            <x-empty-state judul="Belum ada agenda" deskripsi="Agenda kegiatan RT akan tampil di sini setelah dijadwalkan pengurus." />
        @endforelse
    </div>

    @if (method_exists($agenda, 'links'))
        <div class="mt-6">
            {{ $agenda->links() }}
        </div>
    @endif
</x-app-layout>
