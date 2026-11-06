@extends('layouts.dashboard')

@section('title', 'Buat Agenda')
@section('deskripsi', 'Jadwalkan kegiatan RT baru.')

@section('content')
    <x-card>
        <form method="POST" action="{{ route('admin.agenda.store') }}">
            @csrf

            <div>
                <x-input-label for="judul" :value="__('Nama Kegiatan')" />
                <x-text-input id="judul" name="judul" type="text" :value="old('judul')" required placeholder="Contoh: Kerja bakti membersihkan selokan" class="mt-1 block w-full" />
                @error('judul')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="deskripsi" :value="__('Deskripsi')" />
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Keterangan kegiatan (opsional)" class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="tanggal_mulai" :value="__('Mulai')" />
                    <x-text-input id="tanggal_mulai" name="tanggal_mulai" type="datetime-local" :value="old('tanggal_mulai')" required class="mt-1 block w-full" />
                    @error('tanggal_mulai')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="tanggal_selesai" :value="__('Selesai (opsional)')" />
                    <x-text-input id="tanggal_selesai" name="tanggal_selesai" type="datetime-local" :value="old('tanggal_selesai')" class="mt-1 block w-full" />
                    @error('tanggal_selesai')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-input-label for="lokasi" :value="__('Lokasi')" />
                    <x-text-input id="lokasi" name="lokasi" type="text" :value="old('lokasi')" placeholder="Contoh: Balai RT" class="mt-1 block w-full" />
                    @error('lokasi')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Simpan Agenda</button>
                <a href="{{ route('admin.agenda.index') }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
