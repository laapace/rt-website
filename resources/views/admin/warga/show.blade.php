@extends('layouts.dashboard')

@section('title', 'Detail Kartu Keluarga')
@section('deskripsi', 'Data KK beserta daftar anggotanya.')

@section('content')
    <a href="{{ route('admin.warga.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Data Warga</a>

    <x-card class="mt-4">
        <x-slot name="judul">Kartu Keluarga {{ $kk->no_kk }}</x-slot>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-neutral-600">Nomor KK</dt>
                <dd class="font-semibold">{{ $kk->no_kk }}</dd>
            </div>
            <div>
                <dt class="text-neutral-600">Kepala Keluarga</dt>
                <dd class="font-semibold">{{ $kk->kepala_keluarga ?? optional($kk->kepalaKeluarga)->nama ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-600">Alamat</dt>
                <dd class="font-semibold">{{ $kk->alamat }}</dd>
            </div>
            <div>
                <dt class="text-neutral-600">Blok</dt>
                <dd class="font-semibold">{{ $kk->blok }}</dd>
            </div>
            <div>
                <dt class="text-neutral-600">Status Tinggal</dt>
                <dd class="font-semibold"><x-badge :status="$kk->status_tinggal ?? 'aktif'" /></dd>
            </div>
        </dl>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('admin.warga.edit', $kk->id) }}" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Edit Data KK</a>
            <form method="POST" action="{{ route('admin.warga.destroy', $kk->id) }}" onsubmit="return confirm('Hapus Kartu Keluarga ini beserta anggotanya? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-danger-600 px-4 py-2 text-sm font-semibold text-danger-600 hover:bg-red-50">Hapus KK</button>
            </form>
        </div>
    </x-card>

    <h2 class="mb-4 mt-8 text-xl font-bold">Anggota Keluarga</h2>
    @if (($anggota ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NIK</th>
                            <th class="px-4 py-3 font-semibold">Hubungan</th>
                            <th class="px-4 py-3 font-semibold">Jenis Kelamin</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Lahir</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anggota as $warga)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $warga->nama }}</td>
                                <td class="px-4 py-3">{{ $warga->nik }}</td>
                                <td class="px-4 py-3">{{ $warga->hubungan ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $warga->jenis_kelamin ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $warga->tanggal_lahir ? formatTanggal($warga->tanggal_lahir) : '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('admin.warga.anggota.edit', $warga->id) }}" class="font-semibold text-primary-700 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.warga.anggota.destroy', $warga->id) }}" class="inline" onsubmit="return confirm('Hapus anggota ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-2 font-semibold text-danger-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state judul="Belum ada anggota keluarga" deskripsi="Tambahkan anggota keluarga ke Kartu Keluarga ini." />
    @endif

    {{-- Tambah anggota --}}
    <x-card class="mt-6">
        <x-slot name="judul">Tambah Anggota Keluarga</x-slot>
        <form method="POST" action="{{ route('admin.warga.anggota.store', $kk->id) }}">
            @csrf
            <input type="hidden" name="kartu_keluarga_id" value="{{ $kk->id }}">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="nama" :value="__('Nama Lengkap')" />
                    <x-text-input id="nama" name="nama_lengkap" type="text" :value="old('nama_lengkap')" required class="mt-1 block w-full" />
                    @error('nama_lengkap')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="nik" :value="__('NIK')" />
                    <x-text-input id="nik" name="nik" type="text" :value="old('nik')" required inputmode="numeric" maxlength="16" class="mt-1 block w-full" />
                    @error('nik')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="hubungan" :value="__('Hubungan dengan Kepala Keluarga')" />
                    <select id="hubungan" name="hubungan_keluarga" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih —</option>
                        @foreach (['Kepala Keluarga', 'Istri', 'Suami', 'Anak', 'Orang Tua', 'Menantu', 'Cucu', 'Lainnya'] as $hub)
                            <option value="{{ $hub }}" {{ old('hubungan_keluarga') === $hub ? 'selected' : '' }}>{{ $hub }}</option>
                        @endforeach
                    </select>
                    @error('hubungan_keluarga')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih —</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                    <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" :value="old('tanggal_lahir')" class="mt-1 block w-full" />
                    @error('tanggal_lahir')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="status_tinggal" :value="__('Status Tinggal')" />
                    <select id="status_tinggal" name="status_tinggal" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="aktif" selected>Aktif</option>
                        <option value="pindah">Pindah</option>
                        <option value="meninggal">Meninggal</option>
                    </select>
                    @error('status_tinggal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button type="submit" class="mt-5 rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Tambah Anggota</button>
        </form>
    </x-card>
@endsection
