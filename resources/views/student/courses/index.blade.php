<x-app-layout title="Katalog Course">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Katalog course</h1>
                <p class="mt-2 text-gray-500">Pilih course dan mulai belajar dari lesson pertama.</p>
            </div>
            <form method="GET" class="relative w-full sm:w-80">
                <label for="q" class="sr-only">Cari course</label>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.45 4.39l3.33 3.33a.75.75 0 1 1-1.06 1.06l-3.33-3.33A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                <x-text-input id="q" name="q" type="search" placeholder="Cari judul course" value="{{ request('q') }}" class="w-full pl-9" />
            </form>
        </div>

        @if ($courses->isEmpty())
            <x-card class="mt-8">
                <x-empty-state
                    :title="request()->filled('q') ? 'Course tidak ditemukan' : 'Belum ada course'"
                    :description="request()->filled('q') ? 'Coba kata kunci lain.' : 'Course yang sudah dipublikasikan akan tampil di sini.'" />
            </x-card>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>

            <div class="mt-8">{{ $courses->links() }}</div>
        @endif
    </div>
</x-app-layout>
