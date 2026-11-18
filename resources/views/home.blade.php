<x-app-layout>
    @php($featured = $courses->first())

    {{-- Hero --}}
    <section class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div class="grid overflow-hidden rounded-3xl bg-gray-900 lg:grid-cols-2">
            <div class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:py-20">
                <h1 class="text-4xl font-semibold tracking-tight text-white sm:text-5xl">Belajar skill digital dari video singkat dan terarah.</h1>
                <p class="mt-5 max-w-lg text-lg text-gray-300">Course berbahasa Indonesia yang disusun per lesson. Tonton berurutan dari dasar, kapan saja dan dari perangkat apa pun.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-link-button :href="route('courses.index')" class="px-5 py-3">Jelajahi course</x-link-button>
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Daftar gratis</a>
                    @endguest
                </div>
                <dl class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-white/10 pt-8">
                    <div>
                        <dt class="text-sm text-gray-400">Course</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">{{ $courseCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Video lesson</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">{{ $lessonCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Biaya</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">Gratis</dd>
                    </div>
                </dl>
            </div>

            <div class="relative min-h-72 lg:min-h-full">
                <img src="{{ asset('images/hero.jpg') }}" alt="Seorang pelajar tersenyum sambil belajar di depan laptop" class="absolute inset-0 h-full w-full object-cover">
                @if ($featured)
                    <a href="{{ route('courses.show', $featured) }}" class="absolute bottom-6 left-6 right-6 flex items-center gap-4 rounded-2xl bg-white/90 p-3 pe-5 shadow-lg backdrop-blur transition hover:bg-white sm:right-auto sm:max-w-sm">
                        <x-course-thumbnail :course="$featured" class="h-14 w-20 shrink-0 rounded-lg" />
                        <span class="min-w-0">
                            <span class="block text-xs font-medium text-brand-600">Course terbaru</span>
                            <span class="block truncate text-sm font-semibold text-gray-900">{{ $featured->title }}</span>
                            <span class="block text-xs text-gray-500">{{ $featured->lessons_count }} video lesson</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Course terbaru --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-gray-900">Course terbaru</h2>
                <p class="mt-2 text-gray-500">Mulai dari course yang paling baru ditambahkan.</p>
            </div>
            <x-link-button variant="secondary" :href="route('courses.index')">Lihat semua course</x-link-button>
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

    {{-- Manfaat --}}
    <section class="border-y border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="max-w-xl text-3xl font-semibold tracking-tight text-gray-900">Dirancang supaya belajar terasa ringan</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['Video per lesson', 'Materi dipecah menjadi video pendek, jadi mudah dicicil di sela kesibukan.', 'M15.75 10.5l4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z'],
                    ['Urutan yang jelas', 'Setiap course punya daftar lesson berurutan, dari konsep dasar sampai praktik.', 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                    ['Di laptop maupun ponsel', 'Tampilan menyesuaikan layar, jadi kamu bisa lanjut belajar di mana saja.', 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                ] as [$title, $text, $icon])
                    <div class="rounded-2xl bg-gray-50 p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                        </span>
                        <h3 class="mt-5 font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ajakan daftar (hanya untuk guest) --}}
    @guest
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-brand-600 px-8 py-12 sm:flex-row sm:items-center sm:px-12">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Siap mulai belajar?</h2>
                    <p class="mt-2 text-brand-100">Buat akun gratis untuk menonton semua video lesson.</p>
                </div>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-brand-600">Daftar gratis</a>
            </div>
        </section>
    @endguest
</x-app-layout>
