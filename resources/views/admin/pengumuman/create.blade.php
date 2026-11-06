@extends('layouts.dashboard')

@section('title', 'Buat Pengumuman')
@section('deskripsi', 'Pengumuman baru langsung tampil di beranda publik.')

@section('content')
    <x-card>
        <form method="POST" action="{{ route('admin.pengumuman.store') }}" enctype="multipart/form-data">
            @csrf

            <div>
                <x-input-label for="judul" :value="__('Judul Pengumuman')" />
                <x-text-input id="judul" name="judul" type="text" :value="old('judul')" required class="mt-1 block w-full" />
                @error('judul')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="kategori" :value="__('Kategori')" />
                    <select id="kategori" name="kategori" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                        <option value="">— Pilih kategori —</option>
                        @foreach (['umum' => 'Umum', 'keuangan' => 'Keuangan', 'kegiatan' => 'Kegiatan', 'keamanan' => 'Keamanan'] as $nilai => $label)
                            <option value="{{ $nilai }}" {{ old('kategori') === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end pb-1">
                    <label for="is_pinned" class="inline-flex items-center gap-2 text-sm font-medium">
                        <input id="is_pinned" type="checkbox" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }} class="rounded border-neutral-300 text-primary-600 focus:ring-primary-600">
                        Sematkan sebagai pengumuman penting
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <x-input-label for="isi" :value="__('Isi Pengumuman')" />
                <textarea id="isi" name="isi" rows="6" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('isi') }}</textarea>
                @error('isi')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="gambar" :value="__('Lampiran Gambar (opsional)')" />
                <input id="gambar" type="file" name="gambar" accept=".jpg,.jpeg,.png" class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-neutral-600">Format JPG/PNG, maksimal 2 MB.</p>
                @error('gambar')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Terbitkan Pengumuman</button>
                <a href="{{ route('admin.pengumuman.index') }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
