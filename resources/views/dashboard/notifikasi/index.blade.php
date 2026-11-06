@extends('layouts.dashboard')

@section('title', 'Notifikasi')
@section('deskripsi', 'Pemberitahuan tentang tagihan, surat, dan laporan Anda.')

@section('content')
    @if (($notifikasi ?? collect())->count())
        <div class="space-y-3">
            @foreach ($notifikasi as $n)
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)] {{ ($n->dibaca ?? $n->is_read ?? true) ? '' : 'border-l-4 border-l-primary-600' }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold leading-snug">{{ $n->judul ?? 'Notifikasi' }}</p>
                        <span class="text-xs text-neutral-600">{{ formatTanggal($n->created_at) }}</span>
                    </div>
                    <p class="mt-1 text-sm leading-relaxed text-neutral-600">{{ $n->pesan ?? $n->isi ?? '' }}</p>
                </div>
            @endforeach
        </div>
        @if (method_exists($notifikasi, 'links'))
            <div class="mt-4">{{ $notifikasi->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Tidak ada notifikasi" deskripsi="Pemberitahuan tentang tagihan baru, surat yang disetujui, atau perubahan status laporan akan tampil di sini." />
    @endif
@endsection
