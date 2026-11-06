@extends('layouts.dashboard')

@section('title', 'Kelola Surat')
@section('deskripsi', 'Setujui atau tolak pengajuan surat warga, lalu unduh PDF-nya.')

@section('content')
    {{-- Antrean --}}
    <h2 class="mb-4 text-xl font-bold">Antrean Pengajuan</h2>
    @if (($antrean ?? collect())->count())
        <div class="space-y-4">
            @foreach ($antrean as $s)
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">{{ $s->warga->nama ?? 'Warga' }} — {{ $s->jenis }}</p>
                        <x-badge :status="$s->status" />
                    </div>
                    <p class="mt-1 text-sm text-neutral-600">Diajukan {{ formatTanggal($s->created_at) }}</p>
                    <p class="mt-2 text-sm leading-relaxed"><span class="font-semibold">Keperluan: </span>{{ $s->keperluan }}</p>
                    @if ($s->catatan ?? null)
                        <p class="mt-1 text-sm text-neutral-600"><span class="font-semibold">Catatan: </span>{{ $s->catatan }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.surat.setujui', $s->id) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.surat.tolak', $s->id) }}" class="flex flex-wrap gap-2">
                            @csrf
                            <input type="text" name="alasan" placeholder="Alasan penolakan (wajib)" required class="rounded-lg border-neutral-300 text-sm focus:border-primary-600 focus:ring-primary-600">
                            <button type="submit" class="rounded-lg border border-danger-600 px-4 py-2 text-sm font-semibold text-danger-600 hover:bg-red-50">Tolak</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state judul="Tidak ada antrean surat" deskripsi="Pengajuan surat baru dari warga akan muncul di sini." />
    @endif

    {{-- Riwayat --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Riwayat Surat</h2>
    @if (($riwayat ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Pemohon</th>
                            <th class="px-4 py-3 font-semibold">Jenis Surat</th>
                            <th class="px-4 py-3 font-semibold">Nomor Surat</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $s)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $s->warga->nama ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $s->jenis }}</td>
                                <td class="px-4 py-3">{{ $s->nomor_surat ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-badge :status="$s->status" />
                                    @if ($s->status === 'ditolak' && ($s->alasan_penolakan ?? $s->alasan ?? null))
                                        <span class="block text-xs text-danger-600">{{ $s->alasan_penolakan ?? $s->alasan }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($s->status === 'disetujui')
                                        <a href="{{ route('admin.surat.pdf', $s->id) }}" class="font-semibold text-primary-700 hover:underline">Unduh PDF</a>
                                    @else
                                        <span class="text-neutral-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($riwayat, 'links'))
            <div class="mt-4">{{ $riwayat->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada riwayat surat" deskripsi="Surat yang sudah diproses akan tampil di sini." />
    @endif
@endsection
