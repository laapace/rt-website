<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat {{ $surat->jenis ?? '' }} — {{ $surat->nomor_surat ?? '' }}</title>
    <style>
        /* Template PDF Dompdf — CSS inline sederhana saja, tanpa Tailwind. */
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop .nama { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .kop .detail { font-size: 11pt; }
        .judul-surat { text-align: center; margin: 20px 0; }
        .judul-surat .nama-surat { font-size: 14pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .judul-surat .nomor { font-size: 12pt; margin-top: 4px; }
        .isi { text-align: justify; margin: 0 10px; }
        .isi p { margin: 10px 0; }
        table.data { margin: 12px 0 12px 20px; border-collapse: collapse; }
        table.data td { padding: 2px 6px; vertical-align: top; }
        .ttd { margin-top: 40px; width: 100%; }
        .ttd .kanan { float: right; width: 45%; text-align: center; }
        .ttd .spasi { height: 70px; }
    </style>
</head>
<body>
    @php
        $judulJenis = [
            'Surat Keterangan Domisili' => 'SURAT KETERANGAN DOMISILI',
            'Surat Keterangan Tidak Mampu' => 'SURAT KETERANGAN TIDAK MAMPU',
            'Surat Keterangan Usaha' => 'SURAT KETERANGAN USAHA',
            'Surat Keterangan Kelahiran' => 'SURAT KETERANGAN KELAHIRAN',
            'Surat Keterangan Kematian' => 'SURAT KETERANGAN KEMATIAN',
        ];
        $judulSurat = $judulJenis[$surat->jenis] ?? strtoupper($surat->jenis);
        $warga = $surat->warga;
    @endphp

    {{-- Kop surat --}}
    <div class="kop">
        <div class="nama">Rukun Tetangga 02 / Rukun Warga 03</div>
        <div class="detail">Kelurahan {{ $pengaturan->kelurahan ?? '—' }} &nbsp; Kecamatan {{ $pengaturan->kecamatan ?? '—' }} &nbsp; Kota {{ $pengaturan->kota ?? '—' }}</div>
        <div class="detail">Alamat: {{ $pengaturan->alamat ?? '—' }}{{ $pengaturan->telepon ? ' &nbsp;|&nbsp; Telp: ' . $pengaturan->telepon : '' }}</div>
    </div>

    <div class="judul-surat">
        <div class="nama-surat">{{ $judulSurat }}</div>
        <div class="nomor">Nomor: {{ $surat->nomor_surat ?? '—' }}</div>
    </div>

    <div class="isi">
        <p>Yang bertanda tangan di bawah ini, Ketua {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}, menerangkan dengan sebenarnya bahwa:</p>

        <table class="data">
            <tr><td>Nama</td><td>:</td><td>{{ $warga->nama ?? '—' }}</td></tr>
            <tr><td>NIK</td><td>:</td><td>{{ $warga->nik ?? '—' }}</td></tr>
            <tr><td>Tempat / Tanggal Lahir</td><td>:</td><td>{{ $warga->tempat_lahir ?? '—' }}{{ $warga->tanggal_lahir ? ', ' . formatTanggal($warga->tanggal_lahir) : '' }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $warga->jenis_kelamin ?? '—' }}</td></tr>
            <tr><td>Alamat</td><td>:</td><td>{{ $warga->alamat ?? $surat->kartuKeluarga->alamat ?? '—' }}</td></tr>
        </table>

        <p>Adalah benar warga {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}{{ $pengaturan->kelurahan ? ', Kelurahan ' . $pengaturan->kelurahan : '' }}{{ $pengaturan->kecamatan ? ', Kecamatan ' . $pengaturan->kecamatan : '' }}{{ $pengaturan->kota ? ', Kota ' . $pengaturan->kota : '' }}.</p>

        <p>Surat keterangan ini dibuat untuk keperluan: <strong>{{ $surat->keperluan }}</strong>.</p>

        <p>Demikian surat keterangan ini dibuat dengan sebenarnya dan dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="ttd">
        <div class="kanan">
            <p>Dikeluarkan di {{ $pengaturan->kota ?? '—' }} pada {{ formatTanggal($surat->updated_at ?? now()) }}</p>
            <p>Ketua {{ $pengaturan->nama_rt ?? 'RT 02 RW 03' }}</p>
            <div class="spasi"></div>
            <p><strong><u>{{ $pengaturan->nama_ketua ?? '(_________________)' }}</u></strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
