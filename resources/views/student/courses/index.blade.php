<x-app-layout title="Katalog Course">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Katalog Course" subtitle="Pilih course dan mulai belajar lewat video.">
            <x-slot name="actions">
                <form method="GET" class="flex w-full gap-2 sm:w-auto">
                    <label for="q" class="sr-only">Cari course</label>
                    <x-text-input id="q" name="q" type="search" placeholder="Cari course…" value="{{ request('q') }}" class="w-full sm:w-64" />
                    <x-secondary-button type="submit">Cari</x-secondary-button>
                </form>
            </x-slot>
        </x-page-header>

        @if ($courses->isEmpty())
            <x-card>
                <x-empty-state
                    :title="request()->filled('q') ? 'Course tidak ditemukan' : 'Belum ada course'"
                    :description="request()->filled('q') ? 'Coba kata kunci lain.' : 'Course yang sudah dipublikasikan akan tampil di sini.'" />
            </x-card>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-brand-600 to-brand-900">
                            <x-application-logo class="h-12 w-12 opacity-90 transition group-hover:scale-110" />
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="font-semibold text-gray-900 group-hover:text-brand-700">{{ $course->title }}</h2>
                            <p class="mt-2 line-clamp-3 flex-1 text-sm text-gray-600">{{ $course->description }}</p>
                            <p class="mt-4 text-sm font-medium text-gray-500">{{ $course->lessons_count }} lesson</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{ $courses->links() }}
        @endif
    </div>
</x-app-layout>
