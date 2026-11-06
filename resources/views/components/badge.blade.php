{{-- Badge status (design.md §5): SELALU teks + warna, bukan warna saja. --}}
@props(['status' => '', 'teks' => null])

@php
    $kunci = strtolower(str_replace([' ', '-'], '_', (string) $status));

    $peta = [
        // Tagihan
        'belum_bayar'         => ['Belum Bayar', 'border-amber-200 bg-amber-100 text-amber-800'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'border-blue-200 bg-blue-100 text-blue-800'],
        'lunas'               => ['Lunas', 'border-green-200 bg-green-100 text-green-800'],
        'terlambat'           => ['Terlambat', 'border-red-200 bg-red-100 text-red-800'],
        // Pembayaran
        'terverifikasi'       => ['Terverifikasi', 'border-green-200 bg-green-100 text-green-800'],
        'ditolak'             => ['Ditolak', 'border-red-200 bg-red-100 text-red-800'],
        // Surat
        'diajukan'            => ['Diajukan', 'border-blue-200 bg-blue-100 text-blue-800'],
        'diproses'            => ['Diproses', 'border-amber-200 bg-amber-100 text-amber-800'],
        'disetujui'           => ['Disetujui', 'border-green-200 bg-green-100 text-green-800'],
        // Laporan
        'baru'                => ['Baru', 'border-blue-200 bg-blue-100 text-blue-800'],
        'selesai'             => ['Selesai', 'border-green-200 bg-green-100 text-green-800'],
        // Warga
        'aktif'               => ['Aktif', 'border-green-200 bg-green-100 text-green-800'],
        'pindah'              => ['Pindah', 'border-neutral-200 bg-neutral-100 text-neutral-700'],
        'meninggal'           => ['Meninggal', 'border-neutral-200 bg-neutral-100 text-neutral-700'],
        // Pengumuman
        'penting'             => ['Penting', 'border-amber-200 bg-amber-100 text-amber-800'],
    ];

    [$labelBawaan, $kelas] = $peta[$kunci] ?? [null, 'border-neutral-200 bg-neutral-100 text-neutral-700'];
    $label = $teks ?? $labelBawaan ?? $status;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold ' . $kelas]) }}>● {{ $label }}</span>
