@extends('layouts.dashboard')

@section('title', 'Tagihan Saya')
@section('deskripsi', 'Tagihan iuran bulanan Anda dan riwayat pembayaran.')

@section('content')
    {{-- Tagihan bulan berjalan: 1 kartu per tarif aktif (Bulanan + Sampah) --}}
    @if (($tagihanBulanBerjalan ?? collect())->count())
        @foreach ($tagihanBulanBerjalan as $tagihanBerjalan)
        <x-card>
            <x-slot name="judul">Tagihan Bulan Berjalan — {{ $tagihanBerjalan->tarif->nama ?? 'Iuran' }}</x-slot>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-neutral-600">Periode</p>
                    <p class="text-lg font-bold">{{ $tagihanBerjalan->periode }}</p>
                    <p class="mt-2 text-sm text-neutral-600">Nominal</p>
                    <p class="text-2xl font-bold">{{ formatRupiah((int) ($tagihanBerjalan->jumlah ?? $tagihanBerjalan->nominal)) }}</p>
                    <p class="mt-2 text-sm text-neutral-600">Jatuh tempo: {{ formatTanggal($tagihanBerjalan->jatuh_tempo) }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <x-badge :status="$tagihanBerjalan->status" />
                    @if (($tagihanBerjalan->pembayaran_terakhir?->status ?? null) === 'menunggu_verifikasi')
                        <p class="mt-2 text-sm text-neutral-600">Bukti bayar Anda sedang diperiksa Bendahara.</p>
                    @endif
                </div>
            </div>

            @include('dashboard.iuran._form-bayar', ['tagihanForm' => $tagihanBerjalan])
            @if ($tagihanBerjalan->pembayaran_terakhir && ! in_array($tagihanBerjalan->status, ['belum_bayar', 'terlambat']))
                <p class="mt-5 border-t border-neutral-200 pt-5 text-sm">
                    Bukti bayar:
                    <a href="{{ route('dashboard.iuran.bukti', $tagihanBerjalan->pembayaran_terakhir->id) }}" target="_blank" rel="noopener" class="font-semibold text-primary-700 hover:underline">Lihat Bukti</a>
                </p>
            @endif
        </x-card>
        @endforeach
    @else
        <x-empty-state judul="Belum ada tagihan bulan ini" deskripsi="Tagihan dibuat Bendahara tiap awal bulan. Silakan periksa lagi nanti." />
    @endif

    {{-- Tagihan yang dipilih dari riwayat (?bayar=<id>) --}}
    @if ($tagihanDipilih ?? null)
        <div id="bayar" class="mt-6">
            <x-card>
                <x-slot name="judul">Bayar Tagihan — {{ $tagihanDipilih->tarif->nama ?? 'Iuran' }} {{ $tagihanDipilih->periode }}</x-slot>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-neutral-600">Periode</p>
                        <p class="text-lg font-bold">{{ $tagihanDipilih->periode }}</p>
                        <p class="mt-2 text-sm text-neutral-600">Nominal</p>
                        <p class="text-2xl font-bold">{{ formatRupiah((int) ($tagihanDipilih->jumlah ?? $tagihanDipilih->nominal)) }}</p>
                        <p class="mt-2 text-sm text-neutral-600">Jatuh tempo: {{ formatTanggal($tagihanDipilih->jatuh_tempo) }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <x-badge :status="$tagihanDipilih->status" />
                    </div>
                </div>
                @include('dashboard.iuran._form-bayar', ['tagihanForm' => $tagihanDipilih])
            </x-card>
        </div>
    @endif

    {{-- Riwayat tagihan --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Riwayat Tagihan</h2>
    @if (($tagihan ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Periode</th>
                            <th class="px-4 py-3 font-semibold">Jenis Iuran</th>
                            <th class="px-4 py-3 font-semibold">Nominal</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Bayar</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagihan as $t)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $t->periode }}</td>
                                <td class="px-4 py-3">{{ $t->tarif->nama ?? '—' }}</td>
                                <td class="px-4 py-3">{{ formatRupiah((int) ($t->jumlah ?? $t->nominal)) }}</td>
                                <td class="px-4 py-3"><x-badge :status="$t->status" /></td>
                                <td class="px-4 py-3">{{ $t->tanggal_bayar ? formatTanggal($t->tanggal_bayar) : '—' }}</td>
                                <td class="px-4 py-3">
                                    @if (in_array($t->status, ['belum_bayar', 'terlambat']))
                                        <a href="{{ route('dashboard.iuran.index', ['bayar' => $t->id]) }}#bayar" class="font-semibold text-primary-700 hover:underline">Bayar</a>
                                    @elseif ($t->pembayaran_terakhir)
                                        <a href="{{ route('dashboard.iuran.bukti', $t->pembayaran_terakhir->id) }}" target="_blank" rel="noopener" class="font-semibold text-primary-700 hover:underline">Lihat Bukti</a>
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
        <div class="mt-4">{{ $tagihan->links() }}</div>
    @else
        <x-empty-state judul="Belum ada riwayat tagihan" deskripsi="Riwayat pembayaran iuran Anda akan tampil di sini." />
    @endif
@endsection
