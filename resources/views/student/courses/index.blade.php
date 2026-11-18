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
                    <x-course-card :course="$course" />
                @endforeach
            </div>

            {{ $courses->links() }}
        @endif
    </div>
</x-app-layout>
