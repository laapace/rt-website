<x-app-layout>
    <h1 class="text-2xl font-bold sm:text-3xl">Kontak</h1>
    <p class="mt-1 text-sm text-neutral-600">Hubungi pengurus RT untuk keperluan surat, iuran, atau laporan warga.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card>
            <x-slot name="judul">Pengurus RT 02 RW 03</x-slot>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-600">Ketua RT</dt>
                    <dd class="font-semibold">{{ $pengaturan->nama_ketua ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-600">Telepon / WA</dt>
                    <dd class="font-semibold">{{ $pengaturan->telepon ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-600">Alamat</dt>
                    <dd class="text-right font-semibold">{{ $pengaturan->alamat ?? '—' }}, {{ $pengaturan->kelurahan ?? '' }}, {{ $pengaturan->kecamatan ?? '' }}, {{ $pengaturan->kota ?? '' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-600">Jam layanan</dt>
                    <dd class="text-right font-semibold">Senin–Jumat 19.00–21.00 WIB<br>Sabtu–Minggu 09.00–12.00 WIB</dd>
                </div>
            </dl>
            <p class="mt-4 rounded-lg bg-primary-100 px-3 py-2 text-sm text-primary-700">Untuk keperluan mendesak (keamanan, darurat), hubungi Ketua RT atau petugas ronda malam.</p>
        </x-card>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <iframe title="Peta lokasi RT 02 RW 03" src="https://www.google.com/maps?q={{ urlencode(($pengaturan->alamat ?? 'RT 02 RW 03') . ', ' . ($pengaturan->kota ?? '')) }}&output=embed" class="h-72 w-full border-0 lg:h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
        <h2 class="text-lg font-semibold">Sudah jadi warga RT?</h2>
        <p class="mt-1 text-sm text-neutral-600">Masuk ke akun Anda untuk melihat tagihan iuran, mengajukan surat, dan membuat laporan.</p>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('login') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-primary-600">Masuk</a>
            <a href="{{ route('register') }}" class="rounded-lg border border-primary-700 px-5 py-2.5 text-center text-sm font-semibold text-primary-700 hover:bg-primary-100">Daftar sebagai warga</a>
        </div>
    </div>
</x-app-layout>
