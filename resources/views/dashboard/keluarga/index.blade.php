@extends('layouts.dashboard')

@section('title', 'Data Keluarga')
@section('deskripsi', 'Data Kartu Keluarga dan anggota keluarga Anda.')

@section('content')
    @if ($kk ?? null)
        <x-card>
            <x-slot name="judul">Kartu Keluarga</x-slot>
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
        </x-card>

        <h2 class="mb-4 mt-8 text-xl font-bold">Anggota Keluarga</h2>
        @if (($anggota ?? collect())->count())
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="bg-neutral-50 text-neutral-600">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Nama</th>
                                <th class="px-4 py-3 font-semibold">NIK</th>
                                <th class="px-4 py-3 font-semibold">Hubungan</th>
                                <th class="px-4 py-3 font-semibold">Tanggal Lahir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($anggota as $warga)
                                <tr class="border-t border-neutral-200">
                                    <td class="px-4 py-3 font-medium">{{ $warga->nama }}</td>
                                    {{-- NIK disamarkan untuk peran Warga (PRD NFR-02): pakai accessor nik_tersamar. --}}
                                    <td class="px-4 py-3">{{ $warga->nik_tersamar }}</td>
                                    <td class="px-4 py-3">{{ $warga->hubungan ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $warga->tanggal_lahir ? formatTanggal($warga->tanggal_lahir) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-empty-state judul="Belum ada anggota keluarga" deskripsi="Data anggota keluarga Anda belum tercatat." />
        @endif
        <p class="mt-4 text-sm text-neutral-600">Ada data yang berubah (pindah, lahir, meninggal)? Hubungi Sekretaris RT untuk memperbarui data keluarga.</p>
    @else
        <x-empty-state judul="Data keluarga belum terhubung" deskripsi="Akun Anda belum terhubung ke Kartu Keluarga. Pastikan No. KK saat mendaftar sudah benar, atau hubungi Sekretaris RT." />
    @endif
@endsection
