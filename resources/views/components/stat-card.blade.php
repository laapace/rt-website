{{-- Kartu statistik (design.md §3.3): angka besar + label + keterangan. --}}
@props(['label', 'nilai', 'sub' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]']) }}>
    <p class="text-sm text-neutral-600">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold leading-tight text-neutral-900">{{ $nilai }}</p>
    @if ($sub)
        <p class="mt-1 text-sm text-neutral-600">{{ $sub }}</p>
    @endif
</div>
