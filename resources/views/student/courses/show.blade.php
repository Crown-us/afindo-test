<x-app-layout :title="$course->title">
    @php($firstLesson = $course->lessons->first())

    {{-- Header course --}}
    <section class="bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="lg:w-2/3 lg:pe-12">
                <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-400" aria-label="Breadcrumb">
                    <a href="{{ route('courses.index') }}" class="hover:text-white">Katalog</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-gray-300">{{ $course->title }}</span>
                </nav>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">{{ $course->title }}</h1>
                <p class="mt-4 text-lg text-gray-300">{{ $course->description }}</p>
                <p class="mt-6 text-sm text-gray-400">{{ $course->lessons->count() }} video lesson, bahasa Indonesia</p>
            </div>
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        {{-- Kartu aksi: di layar lebar menempel di kanan dan naik ke area header --}}
        <aside class="lg:col-start-3 lg:row-start-1 lg:-mt-52">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg lg:sticky lg:top-24">
                <x-course-thumbnail :course="$course" class="aspect-video w-full" />
                <div class="space-y-4 p-6">
                    @if ($firstLesson)
                        <x-link-button :href="route('courses.lessons.show', [$course, $firstLesson])" class="w-full py-3">
                            {{ auth()->check() ? 'Mulai belajar' : 'Masuk untuk mulai belajar' }}
                        </x-link-button>
                    @endif
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>{{ $course->lessons->count() }} video lesson berurutan</li>
                        <li>Bisa ditonton di laptop dan ponsel</li>
                        <li>Gratis untuk semua akun</li>
                    </ul>
                </div>
            </div>
        </aside>

        {{-- Materi --}}
        <section aria-labelledby="lessons-heading" class="lg:col-span-2 lg:row-start-1">
            <h2 id="lessons-heading" class="text-xl font-semibold text-gray-900">Materi course</h2>
            @guest
                <p class="mt-1 text-sm text-gray-500">Masuk atau daftar untuk menonton videonya.</p>
            @endguest

            <x-card class="mt-4 overflow-hidden">
                <ol class="divide-y divide-gray-100">
                    @forelse ($course->lessons as $lesson)
                        <li>
                            <a href="{{ route('courses.lessons.show', [$course, $lesson]) }}" class="group flex items-start gap-4 px-5 py-4 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 group-hover:bg-brand-600 group-hover:text-white">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.34-5.89a1.5 1.5 0 0 0 0-2.54L6.3 2.84Z" /></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-gray-500">Lesson {{ $loop->iteration }}</span>
                                    <span class="block font-medium text-gray-900">{{ $lesson->title }}</span>
                                    @if ($lesson->description)
                                        <span class="mt-1 block text-sm text-gray-500">{{ $lesson->description }}</span>
                                    @endif
                                </span>
                                @if ($lesson->video_status !== \App\Enums\VideoStatus::Ready)
                                    <x-badge color="amber">Video segera hadir</x-badge>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li><x-empty-state title="Belum ada lesson" description="Materi course ini sedang disiapkan." /></li>
                    @endforelse
                </ol>
            </x-card>
        </section>
    </div>
</x-app-layout>
