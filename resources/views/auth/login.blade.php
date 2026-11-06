<x-guest-layout>
    <h1 class="text-2xl font-bold text-neutral-900">Masuk — Website RT 02 RW 03</h1>
    <p class="mt-1 text-sm text-neutral-600">Masuk dengan email dan kata sandi akun warga Anda.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-4">
        @csrf

        <!-- Alamat Email -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Kata Sandi -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Kata Sandi')" />

            <x-text-input id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Ingat Saya -->
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-neutral-300 text-primary-600 shadow-sm focus:ring-primary-600" name="remember">
                <span class="ms-2 text-sm text-neutral-600">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-neutral-600 underline hover:text-neutral-900" href="{{ route('password.request') }}">
                    {{ __('Lupa kata sandi?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>

        <p class="mt-4 text-center text-sm text-neutral-600">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-primary-700 hover:underline">Daftar sebagai warga</a>
        </p>
    </form>
</x-guest-layout>
