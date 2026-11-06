<x-guest-layout>
    <h1 class="text-2xl font-bold text-neutral-900">Daftar — Website RT 02 RW 03</h1>
    <p class="mt-1 text-sm text-neutral-600">Daftar dengan data yang sama seperti di Kartu Keluarga Anda. Pendaftaran diperiksa pengurus sebelum akun diaktifkan.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-4">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Alamat Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- No. KK -->
        <div class="mt-4">
            <x-input-label for="no_kk" :value="__('Nomor Kartu Keluarga (No. KK)')" />
            <x-text-input id="no_kk" class="mt-1 block w-full" type="text" name="no_kk" :value="old('no_kk')" required inputmode="numeric" maxlength="16" placeholder="16 digit nomor KK" />
            <x-input-error :messages="$errors->get('no_kk')" class="mt-2" />
            <p class="mt-1 text-xs text-neutral-600">No. KK dipakai untuk menghubungkan akun Anda dengan data keluarga.</p>
        </div>

        <!-- NIK -->
        <div class="mt-4">
            <x-input-label for="nik" :value="__('NIK (Nomor Induk Kependudukan)')" />
            <x-text-input id="nik" class="mt-1 block w-full" type="text" name="nik" :value="old('nik')" required inputmode="numeric" maxlength="16" placeholder="16 digit NIK" />
            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            <p class="mt-1 text-xs text-neutral-600">NIK dipakai pengurus untuk mencocokkan Anda dengan data warga.</p>
        </div>

        <!-- No. HP -->
        <div class="mt-4">
            <x-input-label for="no_hp" :value="__('Nomor HP / WhatsApp')" />
            <x-text-input id="no_hp" class="mt-1 block w-full" type="tel" name="no_hp" :value="old('no_hp')" required autocomplete="tel" placeholder="Contoh: 081234567890" />
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <!-- Kata Sandi -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Kata Sandi (minimal 8 karakter)')" />

            <x-text-input id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Ulangi Kata Sandi')" />

            <x-text-input id="password_confirmation" class="mt-1 block w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <a class="text-sm text-neutral-600 underline hover:text-neutral-900" href="{{ route('login') }}">
                {{ __('Sudah punya akun? Masuk') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>

        <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Setelah mendaftar, akun Anda belum langsung aktif. Pengurus akan memeriksa data Anda — Anda akan dihubungi setelah akun diaktifkan.</p>
    </form>
</x-guest-layout>
