<x-app-layout>
    <h1 class="text-2xl font-bold sm:text-3xl">Profil {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}</h1>
    <p class="mt-1 text-sm text-neutral-600">{{ $pengaturan->alamat ?? '' }}{{ ($pengaturan->alamat ?? null) ? ', ' : '' }}{{ $pengaturan->kelurahan ?? 'Kelurahan' }}, {{ $pengaturan->kecamatan ?? 'Kecamatan' }}, {{ $pengaturan->kota ?? 'Kota' }}</p>

    {{-- Sejarah --}}
    <x-card class="mt-6">
        <x-slot name="judul">Sejarah &amp; Tentang RT</x-slot>
        <div class="space-y-3 text-sm leading-relaxed text-neutral-600 sm:text-base">
            @if ($pengaturan->sejarah ?? null)
                @foreach (preg_split('/\R\R+/', $pengaturan->sejarah) as $paragraf)
                    <p>{{ $paragraf }}</p>
                @endforeach
            @else
                <p>RT 02 RW 03 adalah rukun tetangga yang melayani warga dengan semangat gotong royong. Pengurus RT membantu warga mengurus surat pengantar, iuran bulanan, keamanan lingkungan lewat ronda malam, serta kegiatan warga seperti kerja bakti dan perayaan hari besar.</p>
                <p>Sejak 2026, layanan RT dapat diakses secara online melalui website ini: warga bisa melihat tagihan iuran, mengajukan surat, dan melaporkan masalah lingkungan tanpa harus datang ke rumah Ketua RT.</p>
            @endif
        </div>
        @if ($pengaturan->visi ?? null)
            <p class="mt-4 text-sm"><span class="font-semibold text-neutral-900">Visi: </span><span class="text-neutral-600">{{ $pengaturan->visi }}</span></p>
        @endif
    </x-card>

    {{-- Struktur pengurus --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Struktur Pengurus</h2>
    @if (($pengurus ?? collect())->count())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pengurus as $orang)
                @php($jabatan = ucfirst($orang->roles->first()?->peran ?? 'pengurus'))
                <div class="rounded-xl border border-neutral-200 bg-white p-5 text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary-100 text-lg font-bold text-primary-700">{{ strtoupper(mb_substr($orang->name ?? '', 0, 1)) }}</span>
                    <p class="mt-3 font-semibold">{{ $orang->name }}</p>
                    <p class="text-sm text-neutral-600">{{ $jabatan }}</p>
                    {{-- Privasi (design §4.2): nomor WA hanya tampil publik untuk Ketua RT. --}}
                    @if (str_contains(strtolower($jabatan), 'ketua') && ($orang->no_hp ?? null))
                        <p class="mt-2 text-sm text-neutral-600">WA: {{ $orang->no_hp }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state judul="Data pengurus belum diisi" deskripsi="Struktur pengurus RT akan tampil di sini setelah diisi pengurus." />
    @endif

    {{-- Peta lokasi --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Lokasi</h2>
    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
        <iframe title="Peta lokasi RT 02 RW 03" src="https://www.google.com/maps?q={{ urlencode(($pengaturan->alamat ?? 'RT 02 RW 03') . ', ' . ($pengaturan->kota ?? '')) }}&output=embed" class="h-72 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <p class="p-4 text-sm text-neutral-600">{{ $pengaturan->alamat ?? 'Alamat RT 02 RW 03' }}, {{ $pengaturan->kelurahan ?? '' }} {{ $pengaturan->kecamatan ?? '' }}, {{ $pengaturan->kota ?? '' }}</p>
    </div>
</x-app-layout>
