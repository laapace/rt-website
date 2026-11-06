@extends('layouts.dashboard')

@section('title', 'Kelola Iuran')
@section('deskripsi', 'Atur tarif, buat tagihan bulanan, dan verifikasi pembayaran warga.')

@section('content')
    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Tagihan Bulan Ini" :nilai="$ringkasan['total_tagihan'] ?? $ringkasan['total'] ?? 0" sub="Tagihan yang dibuat bulan berjalan" />
        <x-stat-card label="Sudah Lunas" :nilai="($ringkasan['lunas'] ?? 0) . ' KK'" sub="Pembayaran terverifikasi" />
        <x-stat-card label="Menunggu Verifikasi" :nilai="($ringkasan['menunggu'] ?? 0) . ' pembayaran'" sub="Bukti bayar perlu diperiksa" />
        <x-stat-card label="Belum Bayar" :nilai="($ringkasan['belum_bayar'] ?? 0) . ' KK'" sub="Belum mengunggah bukti bayar" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Generate tagihan (hanya Bendahara/Admin — Ketua hanya melihat) --}}
        @can('create', \App\Models\TarifIuran::class)
        <x-card>
            <x-slot name="judul">Buat Tagihan Bulanan</x-slot>
            <form method="POST" action="{{ route('admin.iuran.generate') }}" onsubmit="return confirm('Buat tagihan untuk bulan yang dipilih? Tagihan yang sudah ada tidak akan dibuat ganda.')">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="bulan" :value="__('Bulan')" />
                        <select id="bulan" name="bulan" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                            @foreach (range(1, 12) as $b)
                                <option value="{{ $b }}" {{ (int) old('bulan', date('n')) === $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $b, 1)->locale('id')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select>
                        @error('bulan')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label for="tahun" :value="__('Tahun')" />
                        <x-text-input id="tahun" name="tahun" type="number" :value="old('tahun', date('Y'))" required min="2020" max="2100" class="mt-1 block w-full" />
                        @error('tahun')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Generate Tagihan</button>
            </form>
        </x-card>
        @endcan

        {{-- Tarif iuran aktif: generate membuat 1 tagihan per KK per tarif --}}
        <x-card>
            <x-slot name="judul">Tarif Iuran Aktif</x-slot>
            <ul class="space-y-2 text-sm">
                @forelse (($tarifs ?? collect()) as $trf)
                    <li class="flex items-center justify-between gap-3 rounded-lg bg-neutral-50 px-3 py-2">
                        <span class="font-semibold">{{ $trf->nama }}</span>
                        <span>{{ formatRupiah((int) $trf->nominal) }} / KK / bulan <span class="text-neutral-500">• sejak {{ formatTanggal($trf->periode_mulai) }}</span></span>
                    </li>
                @empty
                    <li class="text-neutral-600">Belum ada tarif aktif.</li>
                @endforelse
            </ul>
            <p class="mt-2 text-xs text-neutral-600">Generate tagihan membuat 1 tagihan per KK untuk setiap tarif aktif di atas (10 KK × {{ ($tarifs ?? collect())->count() }} tarif = {{ 10 * ($tarifs ?? collect())->count() }} tagihan per bulan).</p>

            @can('create', \App\Models\TarifIuran::class)
            <h3 class="mt-5 text-sm font-bold">Tambah Tarif</h3>
            <form method="POST" action="{{ route('admin.iuran.tarif.store') }}" class="mt-2">
                @csrf
                <input type="hidden" name="is_active" value="1">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <x-input-label for="nama" :value="__('Nama Tarif')" />
                        <x-text-input id="nama" name="nama" type="text" :value="old('nama')" required placeholder="cth: Iuran Bulanan" class="mt-1 block w-full" />
                        @error('nama')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label for="nominal" :value="__('Nominal per KK per Bulan (Rp)')" />
                        <x-text-input id="nominal" name="nominal" type="number" :value="old('nominal')" required min="1" step="1000" class="mt-1 block w-full" />
                        @error('nominal')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label for="periode_mulai" :value="__('Berlaku Mulai')" />
                        <x-text-input id="periode_mulai" name="periode_mulai" type="date" :value="old('periode_mulai', date('Y-m-d'))" required class="mt-1 block w-full" />
                        @error('periode_mulai')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Tambah Tarif</button>
            </form>
            @endcan
        </x-card>
    </div>

    {{-- Antrean verifikasi --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Antrean Verifikasi Pembayaran</h2>
    @if (($antrean ?? collect())->count())
        <div class="space-y-4">
            @foreach ($antrean as $bayar)
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold">{{ $bayar->warga->nama ?? $bayar->tagihan->kartuKeluarga->kepala_keluarga ?? 'Warga' }}</p>
                            <p class="text-sm text-neutral-600">Periode {{ $bayar->tagihan->periode ?? '—' }} • {{ formatRupiah((int) ($bayar->nominal_dibayar ?? $bayar->tagihan->nominal ?? 0)) }} • {{ formatTanggal($bayar->created_at) }}</p>
                            @if ($bayar->bukti_path ?? $bayar->bukti_bayar ?? null)
                                <a href="{{ asset('storage/' . ($bayar->bukti_path ?? $bayar->bukti_bayar)) }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm font-semibold text-primary-700 hover:underline">Lihat Bukti Bayar</a>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @can('update', $bayar)
                            <form method="POST" action="{{ route('admin.iuran.verifikasi', $bayar->id) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600">Verifikasi</button>
                            </form>
                            <form method="POST" action="{{ route('admin.iuran.tolak', $bayar->id) }}">
                                @csrf
                                <input type="text" name="alasan" placeholder="Alasan penolakan (wajib)" required class="rounded-lg border-neutral-300 text-sm focus:border-primary-600 focus:ring-primary-600">
                                <button type="submit" class="rounded-lg border border-danger-600 px-4 py-2 text-sm font-semibold text-danger-600 hover:bg-red-50">Tolak</button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state judul="Tidak ada pembayaran menunggu verifikasi" deskripsi="Bukti bayar yang diunggah warga akan muncul di sini untuk diperiksa." />
    @endif

    {{-- Tabel semua tagihan --}}
    <h2 class="mb-4 mt-8 text-xl font-bold">Semua Tagihan</h2>
    @if (($tagihan ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kepala Keluarga</th>
                            <th class="px-4 py-3 font-semibold">Periode</th>
                            <th class="px-4 py-3 font-semibold">Nominal</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagihan as $t)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $t->kartuKeluarga->kepala_keluarga ?? $t->warga->nama ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $t->periode }}</td>
                                <td class="px-4 py-3">{{ formatRupiah((int) ($t->jumlah ?? $t->nominal)) }}</td>
                                <td class="px-4 py-3"><x-badge :status="$t->status" /></td>
                                <td class="px-4 py-3">{{ $t->tanggal_bayar ? formatTanggal($t->tanggal_bayar) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($tagihan, 'links'))
            <div class="mt-4">{{ $tagihan->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada tagihan" deskripsi="Buat tagihan bulanan dengan tombol Generate Tagihan di atas." />
    @endif
@endsection
