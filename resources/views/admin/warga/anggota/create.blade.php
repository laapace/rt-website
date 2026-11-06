@extends('layouts.dashboard')

@section('title', 'Tambah Anggota Keluarga')
@section('deskripsi', 'KK ' . $kartuKeluarga->no_kk . ' — ' . ($kartuKeluarga->kepala_keluarga ?? '—'))

@section('content')
    <a href="{{ route('admin.warga.show', $kartuKeluarga->id) }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Detail KK</a>
    <x-card class="mt-4">
        <x-slot name="judul">Tambah Anggota Keluarga</x-slot>
        <form method="POST" action="{{ route('admin.warga.anggota.store', $kartuKeluarga->id) }}">
            @csrf
            <input type="hidden" name="kartu_keluarga_id" value="{{ $kartuKeluarga->id }}">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="nama_lengkap" :value="__('Nama Lengkap')" />
                    <x-text-input id="nama_lengkap" name="nama_lengkap" type="text" :value="old('nama_lengkap')" required class="mt-1 block w-full" />
                    @error('nama_lengkap')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label for="nik" :value="__('NIK (16 digit)')" />
                    <x-text-input id="nik" name="nik" type="text" :value="old('nik')" required inputmode="numeric" maxlength="16" class="mt-1 block w-full" />
                    @error('nik')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label for="hubungan_keluarga" :value="__('Hubungan Keluarga')" />
                    <select id="hubungan_keluarga" name="hubungan_keluarga" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih —</option>
                        @foreach (['Kepala Keluarga', 'Istri', 'Suami', 'Anak', 'Orang Tua', 'Menantu', 'Cucu', 'Lainnya'] as $hub)
                            <option value="{{ $hub }}" {{ old('hubungan_keluarga') === $hub ? 'selected' : '' }}>{{ $hub }}</option>
                        @endforeach
                    </select>
                    @error('hubungan_keluarga')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih —</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label for="tempat_lahir" :value="__('Tempat Lahir (opsional)')" />
                    <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" :value="old('tempat_lahir')" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir (opsional)')" />
                    <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" :value="old('tanggal_lahir')" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="pekerjaan" :value="__('Pekerjaan (opsional)')" />
                    <x-text-input id="pekerjaan" name="pekerjaan" type="text" :value="old('pekerjaan')" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="status_tinggal" :value="__('Status Tinggal')" />
                    <select id="status_tinggal" name="status_tinggal" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="aktif" selected>Aktif</option>
                        <option value="pindah">Pindah</option>
                        <option value="meninggal">Meninggal</option>
                    </select>
                    @error('status_tinggal')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Anggota</button>
                <a href="{{ route('admin.warga.show', $kartuKeluarga->id) }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
