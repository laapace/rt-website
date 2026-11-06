{{-- Keadaan kosong (design.md §3.3): judul + 1 kalimat + tombol aksi. --}}
@props(['judul', 'deskripsi' => null, 'aksiUrl' => null, 'aksiTeks' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 bg-white p-8 text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]']) }}>
    <p class="text-lg font-semibold text-neutral-900">{{ $judul }}</p>
    @if ($deskripsi)
        <p class="mx-auto mt-1 max-w-md text-sm text-neutral-600">{{ $deskripsi }}</p>
    @endif
    @if ($aksiUrl && $aksiTeks)
        <a href="{{ $aksiUrl }}" class="mt-4 inline-block rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">{{ $aksiTeks }}</a>
    @endif
</div>
