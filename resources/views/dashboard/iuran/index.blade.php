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

            @if (in_array($tagihanBerjalan->status, ['belum_bayar', 'terlambat']))
                <form method="POST" action="{{ route('dashboard.iuran.bayar', $tagihanBerjalan->id) }}" enctype="multipart/form-data" class="mt-5 border-t border-neutral-200 pt-5">
                    @csrf
                    <input type="hidden" name="nominal_dibayar" value="{{ $tagihanBerjalan->nominal }}">
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="metode-{{ $tagihanBerjalan->id }}" class="text-sm font-semibold">Metode Pembayaran</label>
                            <select id="metode-{{ $tagihanBerjalan->id }}" name="metode" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                                <option value="transfer">Transfer</option>
                                <option value="tunai">Tunai (ke Bendahara)</option>
                                <option value="qris">QRIS</option>
                            </select>
                            @error('metode')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tanggal_bayar-{{ $tagihanBerjalan->id }}" class="text-sm font-semibold">Tanggal Bayar</label>
                            <input id="tanggal_bayar-{{ $tagihanBerjalan->id }}" name="tanggal_bayar" type="date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-lg border-neutral-300 px-3 py-2 text-sm">
                            @error('tanggal_bayar')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <label for="bukti-{{ $tagihanBerjalan->id }}" class="mt-4 block text-sm font-semibold">Upload Bukti Bayar</label>
                    <p class="mt-1 text-xs text-neutral-600">Format JPG, PNG, atau PDF — maksimal 2 MB. Bayar via transfer / tunai ke Bendahara, lalu unggah buktinya di sini.</p>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                        <input id="bukti-{{ $tagihanBerjalan->id }}" type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required class="flex-1 rounded-lg border border-neutral-300 px-3 py-2 text-sm">
                        <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Upload Bukti Bayar</button>
                    </div>
                    @error('bukti')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </form>
            @elseif ($tagihanBerjalan->pembayaran_terakhir)
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
                                    @if ($t->pembayaran_terakhir)
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
