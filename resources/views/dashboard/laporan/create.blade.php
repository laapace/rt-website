@extends('layouts.dashboard')

@section('title', 'Buat Laporan')
@section('deskripsi', 'Ceritakan masalah di lingkungan RT — sertakan foto bila ada.')

@section('content')
    <x-card>
        <form method="POST" action="{{ route('dashboard.laporan.store') }}" enctype="multipart/form-data">
            @csrf

            <div>
                <x-input-label for="kategori" :value="__('Kategori Laporan')" />
                <select id="kategori" name="kategori" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                    <option value="">— Pilih kategori —</option>
                    @foreach (['kebersihan' => 'Kebersihan', 'keamanan' => 'Keamanan', 'fasilitas' => 'Fasilitas', 'lainnya' => 'Lain-lain'] as $nilai => $label)
                        <option value="{{ $nilai }}" {{ old('kategori') === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="judul" :value="__('Judul Laporan')" />
                <x-text-input id="judul" name="judul" type="text" :value="old('judul')" required placeholder="Contoh: Lampu jalan mati di Blok B" class="mt-1 block w-full" />
                @error('judul')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="deskripsi" :value="__('Deskripsi')" />
                <textarea id="deskripsi" name="deskripsi" rows="4" required placeholder="Jelaskan masalahnya: di mana, sejak kapan, dan dampaknya" class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="foto" :value="__('Foto (opsional)')" />
                <input id="foto" type="file" name="foto" accept=".jpg,.jpeg,.png" class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-neutral-600">Format JPG/PNG, maksimal 2 MB.</p>
                @error('foto')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Kirim Laporan</button>
                <a href="{{ route('dashboard.laporan.index') }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>
@endsection
