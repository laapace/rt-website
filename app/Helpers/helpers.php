<?php

use Carbon\Carbon;

if (! function_exists('formatRupiah')) {
    /**
     * Format nominal rupiah: 20000 => 'Rp 20.000'.
     */
    function formatRupiah(int $nominal): string
    {
        return 'Rp '.number_format($nominal, 0, ',', '.');
    }
}

if (! function_exists('formatTanggal')) {
    /**
     * Format tanggal Indonesia: '2026-11-12' => '12 Nov 2026'.
     * Menerima string tanggal, DateTime, atau Carbon.
     */
    function formatTanggal($date): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $bulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $tanggal = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $tanggal->format('d').' '.$bulan[(int) $tanggal->format('m')].' '.$tanggal->format('Y');
    }
}

if (! function_exists('formatAgenda')) {
    /**
     * Format tanggal + jam agenda: '08 Nov 2026 • 07:00–10:00 WIB'.
     * Tanpa jam selesai: '08 Nov 2026 • 07:00 WIB'. Tanpa tanggal: '—'.
     */
    function formatAgenda($agenda): string
    {
        if (! $agenda || ! $agenda->tanggal_mulai) {
            return '—';
        }

        $teks = formatTanggal($agenda->tanggal_mulai);
        $mulai = $agenda->tanggal_mulai->format('H:i');
        $selesai = $agenda->tanggal_selesai ? $agenda->tanggal_selesai->format('H:i') : null;

        return $teks.' • '.$mulai.($selesai ? '–'.$selesai : '').' WIB';
    }
}

if (! function_exists('bulanRomawi')) {
    /**
     * Nomor bulan ke angka Romawi: 11 => 'XI' (untuk nomor surat).
     */
    function bulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$bulan] ?? '';
    }
}

if (! function_exists('labelJenisSurat')) {
    /**
     * Label jenis surat dari slug: 'domisili' => 'Surat Keterangan Domisili'.
     */
    function labelJenisSurat(?string $jenis): string
    {
        $peta = [
            'domisili' => 'Surat Keterangan Domisili',
            'tidak_mampu' => 'Surat Keterangan Tidak Mampu',
            'usaha' => 'Surat Keterangan Usaha',
            'kelahiran' => 'Surat Keterangan Kelahiran',
            'kematian' => 'Surat Keterangan Kematian',
            'lainnya' => 'Surat Keterangan',
        ];

        return $peta[$jenis ?? ''] ?? ($jenis ? ucfirst(str_replace('_', ' ', $jenis)) : '—');
    }
}

if (! function_exists('labelKategoriLaporan')) {
    /**
     * Label kategori laporan dari slug: 'kebersihan' => 'Kebersihan'.
     */
    function labelKategoriLaporan(?string $kategori): string
    {
        $peta = [
            'kebersihan' => 'Kebersihan',
            'keamanan' => 'Keamanan',
            'fasilitas' => 'Fasilitas',
            'lainnya' => 'Lain-lain',
        ];

        return $peta[$kategori ?? ''] ?? ($kategori ? ucfirst($kategori) : '—');
    }
}
