{{-- Komponen kartu (design.md §3.3): satu topik per kartu, judul opsional via slot "judul". --}}
@props(['judul' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]']) }}>
    @isset($judul)
        <div class="border-b border-neutral-200 px-5 pb-3 pt-5">
            <h2 class="text-xl font-semibold leading-snug text-neutral-900">{{ $judul }}</h2>
        </div>
    @endisset

    <div class="p-5">
        {{ $slot }}
    </div>
</div>
