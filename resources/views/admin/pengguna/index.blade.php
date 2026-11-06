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
                                <td class="px-4 py-3">{{ $user->kartuKeluarga?->no_kk ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php($peranAda = collect($user->roles ?? [])->map(fn ($r) => is_string($r) ? $r : ($r->peran ?? $r->nama ?? $r->name ?? ''))->filter()->values())
                                    @forelse ($peranAda as $peran)
                                        <span class="mr-1 inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-100 px-2 py-0.5 text-xs font-semibold text-neutral-700">{{ ucfirst($peran) }}
                                            @if (auth()->user()?->hasRole('admin'))
                                                <form method="POST" action="{{ route('admin.pengguna.peran.hapus', $user->id) }}" class="inline" onsubmit="return confirm('Hapus peran {{ ucfirst($peran) }} dari {{ $user->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="peran" value="{{ $peran }}">
                                                    <button type="submit" class="font-bold text-danger-600 hover:underline" title="Hapus peran {{ ucfirst($peran) }}">×</button>
                                                </form>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-neutral-600">Warga</span>
                                    @endforelse
                                    @php($peranSisa = collect(['warga', 'ketua', 'sekretaris', 'bendahara', 'admin'])->diff($peranAda)->values())
                                    @if (auth()->user()?->hasRole('admin') && $peranSisa->count())
                                        <form method="POST" action="{{ route('admin.pengguna.peran.tambah', $user->id) }}" class="mt-2 flex items-center gap-1">
                                            @csrf
                                            <label class="sr-only" for="peran-{{ $user->id }}">Tambah peran</label>
                                            <select id="peran-{{ $user->id }}" name="peran" class="rounded-lg border-neutral-300 text-xs focus:border-primary-600 focus:ring-primary-600">
                                                @foreach ($peranSisa as $p)
                                                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg bg-primary-700 px-2 py-1 text-xs font-semibold text-white hover:bg-primary-600">Tambah</button>
                                        </form>
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
