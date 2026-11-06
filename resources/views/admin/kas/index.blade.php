@extends('layouts.dashboard')

@section('title', 'Kas RT')
@section('deskripsi', 'Saldo berjalan, kas masuk, dan kas keluar RT.')

@section('content')
    {{-- 3 kartu saldo --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Saldo Saat Ini" :nilai="formatRupiah((int) ($saldo['saldo'] ?? 0))" sub="Total masuk dikurangi total keluar" />
        <x-stat-card label="Masuk Bulan Ini" :nilai="formatRupiah((int) ($saldo['total_masuk'] ?? 0))" :sub="'Bulan ' . ($bulan ?? date('F Y'))" />
        <x-stat-card label="Keluar Bulan Ini" :nilai="formatRupiah((int) ($saldo['total_keluar'] ?? 0))" :sub="'Bulan ' . ($bulan ?? date('F Y'))" />
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.kas.index') }}" class="flex gap-3">
            <label class="sr-only" for="bulan">Bulan</label>
            <input id="bulan" type="month" name="bulan" value="{{ $bulan ?? date('Y-m') }}" class="rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
            <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Tampilkan</button>
        </form>
        <a href="{{ route('admin.kas.export') }}" class="rounded-lg border border-primary-700 px-5 py-2.5 text-center text-sm font-semibold text-primary-700 hover:bg-primary-100">Ekspor CSV</a>
    </div>

    {{-- Form catat kas (hanya Bendahara/Admin — Ketua hanya melihat) --}}
    @can('create', \App\Models\Kas::class)
    <x-card class="mt-6">
        <x-slot name="judul">Catat Kas Masuk / Keluar</x-slot>
        <form method="POST" action="{{ route('admin.kas.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="jenis" :value="__('Jenis Kas')" />
                    <select id="jenis" name="jenis" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="masuk" {{ old('jenis') === 'masuk' ? 'selected' : '' }}>Kas Masuk</option>
                        <option value="keluar" {{ old('jenis') === 'keluar' ? 'selected' : '' }}>Kas Keluar</option>
                    </select>
                    @error('jenis')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="kategori" :value="__('Kategori')" />
                    <select id="kategori" name="kategori" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih kategori —</option>
                        @foreach (['Iuran', 'Sumbangan', 'Operasional', 'Kegiatan', 'Perbaikan', 'Lain-lain'] as $kat)
                            <option value="{{ $kat }}" {{ old('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="nominal" :value="__('Nominal (Rp)')" />
                    <x-text-input id="nominal" name="nominal" type="number" :value="old('nominal')" required min="1" step="1000" class="mt-1 block w-full" />
                    @error('nominal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="tanggal" :value="__('Tanggal')" />
                    <x-text-input id="tanggal" name="tanggal" type="date" :value="old('tanggal', date('Y-m-d'))" required class="mt-1 block w-full" />
                    @error('tanggal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="keterangan" :value="__('Keterangan')" />
                    <x-text-input id="keterangan" name="keterangan" type="text" :value="old('keterangan')" required placeholder="Contoh: Iuran bulan November Blok A" class="mt-1 block w-full" />
                    @error('keterangan')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="bukti" :value="__('Bukti (opsional)')" />
                    <input id="bukti" type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-neutral-600">Format JPG/PNG/PDF, maksimal 2 MB.</p>
                    @error('bukti')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button type="submit" class="mt-5 rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Catatan Kas</button>
        </form>
    </x-card>
    @endcan

    {{-- Riwayat kas --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Riwayat Kas</h2>
    @if (($kas ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Jenis</th>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Keterangan</th>
                            <th class="px-4 py-3 font-semibold">Nominal</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kas as $k)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3">{{ formatTanggal($k->tanggal) }}</td>
                                <td class="px-4 py-3">
                                    @if (($k->jenis ?? $k->tipe) === 'masuk')
                                        <span class="font-semibold text-success-600">Masuk</span>
                                    @else
                                        <span class="font-semibold text-danger-600">Keluar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $k->kategori }}</td>
                                <td class="px-4 py-3">{{ $k->keterangan }}</td>
                                <td class="px-4 py-3 font-semibold">{{ formatRupiah((int) $k->nominal) }}</td>
                                <td class="px-4 py-3">
                                    @can('delete', $k)
                                    <form method="POST" action="{{ route('admin.kas.destroy', $k->id) }}" onsubmit="return confirm('Hapus catatan kas ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-danger-600 hover:underline">Hapus</button>
                                    </form>
                                    @else
                                        —
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($kas, 'links'))
            <div class="mt-4">{{ $kas->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada catatan kas" deskripsi="Catatan kas masuk dan keluar akan tampil di sini." />
    @endif
@endsection
