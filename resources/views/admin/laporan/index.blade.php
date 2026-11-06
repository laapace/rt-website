@extends('layouts.dashboard')

@section('title', 'Kelola Laporan')
@section('deskripsi', 'Tindak lanjuti laporan warga dan beri komentar.')

@section('content')
    @if (($laporan ?? collect())->count())
        <div class="space-y-4">
            @foreach ($laporan as $l)
                <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-primary-100 px-2.5 py-0.5 font-semibold text-primary-700">{{ labelKategoriLaporan($l->kategori) }}</span>
                            <span class="text-neutral-600">{{ $l->warga->nama ?? 'Warga' }} • {{ formatTanggal($l->created_at) }}</span>
                        </div>
                        <x-badge :status="$l->status" />
                    </div>
                    <h2 class="mt-2 text-lg font-semibold leading-snug">{{ $l->judul }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-neutral-600">{{ $l->deskripsi }}</p>
                    @if ($l->foto_path ?? null)
                        <img src="{{ asset('storage/' . $l->foto_path) }}" alt="Foto laporan: {{ $l->judul }}" class="mt-3 max-h-64 rounded-lg object-cover">
                    @endif
                    @if ($l->komentar_pengurus ?? null)
                        <p class="mt-3 rounded-lg bg-neutral-50 px-3 py-2 text-sm"><span class="font-semibold">Komentar pengurus: </span>{{ $l->komentar_pengurus }}</p>
                    @endif

                    <form method="POST" action="{{ route('admin.laporan.status', $l->id) }}" class="mt-4 border-t border-neutral-200 pt-4">
                        @csrf
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <label class="sr-only" for="status-{{ $l->id }}">Ubah status</label>
                            <select id="status-{{ $l->id }}" name="status" required class="rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600 sm:w-48">
                                @foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $nilai => $label)
                                    <option value="{{ $nilai }}" {{ $l->status === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="sr-only" for="komentar-{{ $l->id }}">Komentar pengurus</label>
                            <input id="komentar-{{ $l->id }}" type="text" name="komentar_pengurus" value="{{ old('komentar_pengurus', $l->komentar_pengurus) }}" placeholder="Komentar untuk warga (opsional)" class="flex-1 rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                            <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Simpan Status</button>
                        </div>
                        @error('status')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </form>
                </article>
            @endforeach
        </div>
        @if (method_exists($laporan, 'links'))
            <div class="mt-4">{{ $laporan->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada laporan warga" deskripsi="Laporan dari warga (kebersihan, keamanan, fasilitas) akan muncul di sini." />
    @endif
@endsection
