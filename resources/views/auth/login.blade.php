<x-guest-layout>
    <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Masuk ke akunmu</h1>
    <p class="mt-2 text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700">Daftar gratis</a>
    </p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-brand-600 hover:text-brand-700" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" name="remember">
            <span class="text-sm text-gray-600">Ingat saya</span>
        </label>

        <x-primary-button class="w-full py-2.5">Masuk</x-primary-button>
    </form>
</x-guest-layout>
