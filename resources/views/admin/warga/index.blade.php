@extends('layouts.dashboard')

@section('title', 'Data Warga')
@section('deskripsi', 'Daftar Kartu Keluarga — cari, filter, tambah, dan kelola anggota.')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.warga.index') }}" class="flex flex-1 flex-col gap-3 sm:flex-row">
            <label class="sr-only" for="q">Cari warga</label>
            <input id="q" type="search" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari nama / NIK / No. KK…" class="flex-1 rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
            <label class="sr-only" for="blok">Blok</label>
            <select id="blok" name="blok" class="rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600 sm:w-32">
                <option value="">Semua blok</option>
                @foreach (['A', 'B', 'C', 'D', 'E'] as $b)
                    <option value="{{ $b }}" {{ ($blok ?? request('blok')) === $b ? 'selected' : '' }}>Blok {{ $b }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Cari</button>
        </form>
        <a href="{{ route('admin.warga.create') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-primary-600">+ Tambah KK</a>
    </div>

    @if (($kk ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kepala Keluarga</th>
                            <th class="px-4 py-3 font-semibold">No. KK</th>
                            <th class="px-4 py-3 font-semibold">Blok</th>
                            <th class="px-4 py-3 font-semibold">Jumlah Anggota</th>
                            <th class="px-4 py-3 font-semibold">Status Tinggal</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kk as $item)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $item->kepala_keluarga ?? optional($item->kepalaKeluarga)->nama ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item->no_kk }}</td>
                                <td class="px-4 py-3">{{ $item->blok }}</td>
                                <td class="px-4 py-3">{{ $item->warga_count ?? $item->anggota_count ?? $item->warga->count() ?? '—' }}</td>
                                <td class="px-4 py-3"><x-badge :status="$item->status_tinggal ?? 'aktif'" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.warga.show', $item->id) }}" class="font-semibold text-primary-700 hover:underline">Lihat</a>
                                    <span class="text-neutral-300"> | </span>
                                    <a href="{{ route('admin.warga.edit', $item->id) }}" class="font-semibold text-primary-700 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $kk->links() }}</div>
    @else
        <x-empty-state judul="Tidak ada data warga" deskripsi="Belum ada Kartu Keluarga yang cocok dengan pencarian ini, atau data warga belum diinput." :aksiUrl="route('admin.warga.create')" aksiTeks="Tambah KK" />
    @endif
@endsection
