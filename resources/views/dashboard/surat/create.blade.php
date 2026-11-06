@extends('layouts.dashboard')

@section('title', 'Ajukan Surat')
@section('deskripsi', 'Pilih jenis surat, tulis keperluan, lalu kirim pengajuan Anda.')

@section('content')
    @if (! auth()->user()?->warga_id)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-relaxed text-amber-800">
            Akun Anda belum terhubung dengan data warga, jadi pengajuan surat tidak bisa dikirim. Gunakan akun warga, atau hubungi Sekretaris RT.
        </div>
    @endif
    <x-card>
        <form method="POST" action="{{ route('dashboard.surat.store') }}">
            @csrf

            <div>
                <x-input-label for="jenis" :value="__('Jenis Surat')" />
                <select id="jenis" name="jenis" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                    <option value="">— Pilih jenis surat —</option>
                    @foreach (['domisili' => 'Surat Keterangan Domisili', 'tidak_mampu' => 'Surat Keterangan Tidak Mampu', 'usaha' => 'Surat Keterangan Usaha', 'kelahiran' => 'Surat Keterangan Kelahiran', 'kematian' => 'Surat Keterangan Kematian', 'lainnya' => 'Surat Keterangan Lainnya'] as $nilai => $label)
                        <option value="{{ $nilai }}" {{ old('jenis') === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="keperluan" :value="__('Keperluan')" />
                <textarea id="keperluan" name="keperluan" rows="4" required placeholder="Contoh: Untuk keperluan pendaftaran sekolah anak" class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('keperluan') }}</textarea>
                @error('keperluan')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-input-label for="catatan" :value="__('Catatan (opsional)')" />
                <textarea id="catatan" name="data_surat[catatan]" rows="2" placeholder="Keterangan tambahan bila ada" class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">{{ old('data_surat.catatan') }}</textarea>
                @error('catatan')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Kirim Pengajuan Surat</button>
                <a href="{{ route('dashboard.surat.index') }}" class="rounded-lg border border-neutral-300 px-5 py-2.5 text-center text-sm font-semibold text-neutral-600 hover:bg-neutral-100">Batal</a>
            </div>
        </form>
    </x-card>

    <p class="mt-4 text-sm text-neutral-600">Setelah dikirim, status pengajuan bisa dilacak di halaman Surat Saya. Nomor surat dibuat otomatis saat pengajuan disetujui pengurus.</p>
@endsection
