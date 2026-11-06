@extends('layouts.dashboard')

@section('title', 'Tambah Kartu Keluarga')
@section('deskripsi', 'Isi data KK baru — setelah disimpan, tambahkan anggota keluarganya.')

@section('content')
    <x-card>
        <form method="POST" action="{{ route('admin.warga.store') }}">
            @csrf

            <input type="hidden" name="rt" value="02">
            <input type="hidden" name="rw" value="03">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="no_kk" :value="__('Nomor Kartu Keluarga (No. KK)')" />
                    <x-text-input id="no_kk" name="no_kk" type="text" :value="old('no_kk')" required inputmode="numeric" maxlength="16" placeholder="16 digit nomor KK" class="mt-1 block w-full" />
                    @error('no_kk')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="blok" :value="__('Blok')" />
                    <select id="blok" name="blok" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih blok —</option>
                        @foreach (['Blok A', 'Blok B', 'Blok C', 'Blok D', 'Blok E'] as $b)
                            <option value="{{ $b }}" {{ old('blok') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                    @error('blok')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                    <textarea id="alamat" name="alamat" rows="2" required placeholder="Contoh: Jl. Melati No. 12" class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="nama_kepala" :value="__('Nama Kepala Keluarga')" />
                    <x-text-input id="nama_kepala" name="nama_kepala" type="text" :value="old('nama_kepala')" required class="mt-1 block w-full" />
                    @error('nama_kepala')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="nik_kepala" :value="__('NIK Kepala Keluarga')" />
                    <x-text-input id="nik_kepala" name="nik_kepala" type="text" :value="old('nik_kepala')" required inputmode="numeric" maxlength="16" class="mt-1 block w-full" />
                    @error('nik_kepala')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="status_tinggal" :value="__('Status Tinggal')" />
                    <select id="status_tinggal" name="status_tinggal" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="aktif" {{ old('status_tinggal', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="pindah" {{ old('status_tinggal') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="meninggal" {{ old('status_tinggal') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                    @error('status_tinggal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Kartu Keluarga</button>
                <a href="{{ route('admin.warga.index') }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
