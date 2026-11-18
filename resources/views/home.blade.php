<x-app-layout>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-200">Belajar lewat video</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Kuasai skill baru, satu lesson setiap kali.</h1>
                <p class="mt-5 max-w-xl text-lg text-brand-100">
                    Course singkat dan terstruktur dari dasar. Tonton materi video sesuai urutan, kapan saja, dari perangkat apa pun.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-800 shadow-sm hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-brand-800">Jelajahi course</a>
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg border border-white/40 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white">Daftar gratis</a>
                    @endguest
                </div>
            </div>

            {{-- Ilustrasi sederhana: mockup player --}}
            <div class="hidden lg:block" aria-hidden="true">
                <div class="rounded-2xl bg-white/10 p-3 shadow-2xl ring-1 ring-white/20">
                    <div class="flex aspect-video items-center justify-center rounded-xl bg-brand-950/60">
                        <x-application-logo class="h-20 w-20" />
                    </div>
                    <div class="mt-3 space-y-2 px-1 pb-1">
                        @foreach (['w-3/4', 'w-1/2', 'w-2/3'] as $width)
                            <div class="flex items-center gap-3">
                                <span class="h-6 w-6 shrink-0 rounded-full bg-white/20"></span>
                                <span class="h-2.5 {{ $width }} rounded-full bg-white/25"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Course terbaru --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">Course terbaru</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">Mulai dari sini</h2>
            </div>
            <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">Lihat semua course &rarr;</a>
        </div>

        @if ($courses->isEmpty())
            <x-card class="mt-8">
                <x-empty-state title="Belum ada course" description="Course yang sudah dipublikasikan akan tampil di sini." />
            </x-card>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Cara belajar --}}
    <section class="border-y border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">Cara belajar</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">Sederhana dan terarah</h2>
            <div class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['1', 'Pilih course', 'Lihat katalog dan baca ringkasan setiap course sebelum mulai.'],
                    ['2', 'Tonton berurutan', 'Materi disusun per lesson, dari dasar sampai lanjutan.'],
                    ['3', 'Belajar di mana saja', 'Tampilan menyesuaikan layar laptop maupun ponsel.'],
                ] as [$step, $title, $text])
                    <div class="rounded-xl border border-gray-200 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-700">{{ $step }}</span>
                        <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    @guest
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-6 rounded-2xl bg-brand-50 p-8 ring-1 ring-brand-100 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Siap mulai belajar?</h2>
                    <p class="mt-1 text-gray-600">Buat akun gratis untuk menonton seluruh materi video.</p>
                </div>
                <x-link-button :href="route('register')">Daftar sekarang</x-link-button>
            </div>
        </section>
    @endguest

    <footer class="border-t border-gray-200 py-8 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Afindo Learn
    </footer>
</x-app-layout>
