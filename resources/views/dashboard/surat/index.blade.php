@extends('layouts.dashboard')

@section('title', 'Surat Saya')
@section('deskripsi', 'Ajukan surat pengantar dan lacak statusnya di sini.')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('dashboard.surat.create') }}" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">+ Ajukan Surat</a>
    </div>

    @if (($surat ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Jenis Surat</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Ajukan</th>
                            <th class="px-4 py-3 font-semibold">Nomor Surat</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surat as $s)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $s->jenis }}</td>
                                <td class="px-4 py-3">{{ formatTanggal($s->created_at) }}</td>
                                <td class="px-4 py-3">{{ $s->nomor_surat ?? '—' }}</td>
                                <td class="px-4 py-3"><x-badge :status="$s->status" /></td>
                                <td class="px-4 py-3">
                                    @if ($s->status === 'disetujui')
                                        <a href="{{ route('dashboard.surat.pdf', $s->id) }}" class="font-semibold text-primary-700 hover:underline">Unduh PDF</a>
                                    @elseif ($s->status === 'ditolak' && ($s->alasan_penolakan ?? $s->alasan ?? null))
                                        <span class="text-danger-600">Alasan: {{ $s->alasan_penolakan ?? $s->alasan }}</span>
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
        @if (method_exists($surat, 'links'))
            <div class="mt-4">{{ $surat->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada pengajuan surat" deskripsi="Ajukan surat domisili atau keterangan lainnya secara online — tidak perlu antre ke rumah Ketua RT." :aksiUrl="route('dashboard.surat.create')" aksiTeks="Ajukan Surat" />
    @endif
@endsection
