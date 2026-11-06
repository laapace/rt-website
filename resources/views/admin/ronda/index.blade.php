@extends('layouts.dashboard')

@section('title', 'Kelola Ronda')
@section('deskripsi', 'Buat jadwal ronda malam per tanggal dan blok.')

@section('content')
    <x-card>
        <x-slot name="judul">Buat Jadwal Ronda</x-slot>
        <form method="POST" action="{{ route('admin.ronda.store') }}">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="tanggal" :value="__('Tanggal Ronda')" />
                    <x-text-input id="tanggal" name="tanggal" type="date" :value="old('tanggal')" required class="mt-1 block w-full" />
                    @error('tanggal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="blok" :value="__('Blok')" />
                    <select id="blok" name="blok" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih blok —</option>
                        @foreach (['A', 'B', 'C', 'D', 'E'] as $b)
                            <option value="{{ $b }}" {{ old('blok') === $b ? 'selected' : '' }}>Blok {{ $b }}</option>
                        @endforeach
                    </select>
                    @error('blok')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="waktu" :value="__('Waktu')" />
                    <x-text-input id="waktu" name="waktu" type="text" :value="old('waktu', '22.00–04.00 WIB')" class="mt-1 block w-full" />
                    @error('waktu')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="petugas" :value="__('Petugas (pisahkan nama dengan koma)')" />
                    <x-text-input id="petugas" name="petugas" type="text" :value="old('petugas')" required placeholder="Contoh: Budi, Agus, Slamet" class="mt-1 block w-full" />
                    @error('petugas')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button type="submit" class="mt-5 rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Jadwal Ronda</button>
        </form>
    </x-card>

    <h2 class="mb-4 mt-8 text-xl font-bold">Daftar Jadwal Ronda</h2>
    @if (($jadwal ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Blok</th>
                            <th class="px-4 py-3 font-semibold">Waktu</th>
                            <th class="px-4 py-3 font-semibold">Petugas</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwal as $j)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ formatTanggal($j->tanggal) }}</td>
                                <td class="px-4 py-3">{{ $j->blok }}</td>
                                <td class="px-4 py-3">{{ $j->waktu ?? '22.00–04.00 WIB' }}</td>
                                <td class="px-4 py-3">
                                    @if (is_iterable($j->petugas ?? null))
                                        {{ collect($j->petugas)->map(fn ($p) => is_string($p) ? $p : ($p->nama ?? ''))->filter()->implode(', ') }}
                                    @else
                                        {{ $j->petugas ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.ronda.destroy', $j->id) }}" onsubmit="return confirm('Hapus jadwal ronda ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-danger-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($jadwal, 'links'))
            <div class="mt-4">{{ $jadwal->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada jadwal ronda" deskripsi="Buat jadwal ronda pertama dengan form di atas." />
    @endif
@endsection
