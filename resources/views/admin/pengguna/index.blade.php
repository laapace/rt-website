@extends('layouts.dashboard')

@section('title', 'Kelola Pengguna')
@section('deskripsi', 'Daftar akun pengguna — aktifkan akun warga setelah datanya cocok dengan KK.')

@section('content')
    @if (($users ?? collect())->count())
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">No. KK</th>
                            <th class="px-4 py-3 font-semibold">Peran</th>
                            <th class="px-4 py-3 font-semibold">Status Akun</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->no_kk ?? $user->kartu_keluarga_id ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if (is_iterable($user->roles ?? null))
                                        {{ collect($user->roles)->map(fn ($r) => is_string($r) ? $r : ($r->nama ?? $r->name ?? ''))->filter()->implode(', ') ?: 'Warga' }}
                                    @else
                                        {{ $user->peran ?? 'Warga' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->is_active ?? $user->aktif ?? true)
                                        <x-badge status="aktif" teks="Aktif" />
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">● Menunggu Aktivasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @unless ($user->is_active ?? $user->aktif ?? true)
                                        <form method="POST" action="{{ route('admin.pengguna.aktifkan', $user->id) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-primary-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-600">Aktifkan</button>
                                        </form>
                                    @else
                                        <span class="text-neutral-600">—</span>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($users, 'links'))
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    @else
        <x-empty-state judul="Belum ada pengguna" deskripsi="Akun pengguna yang mendaftar akan tampil di sini untuk diperiksa dan diaktifkan." />
    @endif
    <p class="mt-4 text-sm text-neutral-600">Akun warga baru tidak langsung aktif — Sekretaris mengaktifkan setelah No. KK dan NIK cocok dengan data warga (FR-02).</p>
@endsection
