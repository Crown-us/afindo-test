@php
    // Menu utama: [nama route, label, pola route aktif]. Admin mendapat menu kelola tambahan.
    $navItems = [['home', 'Beranda', 'home'], ['courses.index', 'Katalog', 'courses.*']];
    if (Auth::user()?->isAdmin()) {
        $navItems[] = ['admin.dashboard', 'Dashboard', 'admin.dashboard'];
        $navItems[] = ['admin.courses.index', 'Kelola Course', 'admin.courses.*'];
    }
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-brand />
            </a>
            <div class="hidden items-center gap-1 md:flex">
                @foreach ($navItems as [$route, $label, $pattern])
                    <x-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-nav-link>
                @endforeach
            </div>
        </div>

        {{-- Desktop: tombol masuk/daftar atau menu akun --}}
        <div class="hidden md:flex md:items-center md:gap-2">
            @guest
                <x-link-button variant="secondary" :href="route('login')">Masuk</x-link-button>
                <x-link-button :href="route('register')">Daftar gratis</x-link-button>
            @else
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-left hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">{{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="leading-tight">
                                <span class="block text-sm font-medium text-gray-900">{{ Auth::user()->name }}</span>
                                <span class="block text-xs text-gray-500">{{ Auth::user()->role->label() }}</span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @endguest
        </div>

        {{-- Tombol menu mobile --}}
        <button @click="open = ! open" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 md:hidden" :aria-expanded="open" aria-label="Buka menu">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': ! open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Menu mobile --}}
    <div x-show="open" x-cloak class="space-y-1 border-t border-gray-200 bg-white px-4 py-3 md:hidden">
        @foreach ($navItems as [$route, $label, $pattern])
            <x-responsive-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-responsive-nav-link>
        @endforeach

        <div class="mt-3 border-t border-gray-200 pt-3">
            @guest
                <div class="flex gap-2">
                    <x-link-button variant="secondary" :href="route('login')" class="flex-1">Masuk</x-link-button>
                    <x-link-button :href="route('register')" class="flex-1">Daftar gratis</x-link-button>
                </div>
            @else
                <p class="px-3 text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                <p class="mb-2 px-3 text-sm text-gray-500">{{ Auth::user()->email }}</p>
                <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-responsive-nav-link>
                </form>
            @endguest
        </div>
    </div>
</nav>
