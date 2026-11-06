@extends('layouts.dashboard')

@section('title', 'Edit Kartu Keluarga')
@section('deskripsi', 'Perbarui data Kartu Keluarga ' . $kk->no_kk . '.')

@section('content')
    <x-card>
        <form method="POST" action="{{ route('admin.warga.update', $kk->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="rt" value="{{ $kk->rt }}">
            <input type="hidden" name="rw" value="{{ $kk->rw }}">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="no_kk" :value="__('Nomor Kartu Keluarga (No. KK)')" />
                    <x-text-input id="no_kk" name="no_kk" type="text" :value="old('no_kk', $kk->no_kk)" required inputmode="numeric" maxlength="16" class="mt-1 block w-full" />
                    @error('no_kk')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="blok" :value="__('Blok')" />
                    <select id="blok" name="blok" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih blok —</option>
                        @foreach (['Blok A', 'Blok B', 'Blok C', 'Blok D', 'Blok E'] as $b)
                            <option value="{{ $b }}" {{ old('blok', $kk->blok) === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                    @error('blok')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                    <textarea id="alamat" name="alamat" rows="2" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('alamat', $kk->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="kepala_keluarga" :value="__('Nama Kepala Keluarga')" />
                    <x-text-input id="kepala_keluarga" name="kepala_keluarga" type="text" :value="old('kepala_keluarga', $kk->kepala_keluarga ?? optional($kk->kepalaKeluarga)->nama)" required class="mt-1 block w-full" />
                    @error('kepala_keluarga')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="status_tinggal" :value="__('Status Tinggal')" />
                    <select id="status_tinggal" name="status_tinggal" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        @foreach (['aktif' => 'Aktif', 'pindah' => 'Pindah', 'meninggal' => 'Meninggal'] as $nilai => $label)
                            <option value="{{ $nilai }}" {{ old('status_tinggal', $kk->status_tinggal ?? 'aktif') === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status_tinggal')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Perubahan</button>
                <a href="{{ route('admin.warga.show', $kk->id) }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
