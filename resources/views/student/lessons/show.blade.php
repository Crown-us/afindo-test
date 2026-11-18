<x-app-layout :title="$lesson->title">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('courses.index') }}" class="hover:text-gray-700">Katalog Course</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('courses.show', $course) }}" class="hover:text-gray-700">{{ $course->title }}</a>
        </nav>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Player --}}
            <div class="space-y-4 lg:col-span-2">
                @if ($lesson->video_status === \App\Enums\VideoStatus::Ready)
                    <video src="{{ route('lessons.video', $lesson) }}" controls controlsList="nodownload" preload="metadata"
                           class="aspect-video w-full rounded-xl bg-black"></video>
                @else
                    <div class="flex aspect-video w-full flex-col items-center justify-center rounded-xl bg-gray-900 px-6 text-center text-gray-300">
                        <p class="font-semibold text-white">Video belum tersedia</p>
                        <p class="mt-1 text-sm">Materi video lesson ini sedang disiapkan.</p>
                    </div>
                @endif

                <div>
                    <p class="text-sm font-medium text-brand-700">Lesson {{ $number }} dari {{ $lessons->count() }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">{{ $lesson->title }}</h1>
                    @if ($lesson->description)
                        <p class="mt-2 whitespace-pre-line text-gray-700">{{ $lesson->description }}</p>
                    @endif
                </div>

                <div class="flex justify-between gap-3 border-t border-gray-200 pt-4">
                    @if ($previous)
                        <x-link-button variant="secondary" :href="route('courses.lessons.show', [$course, $previous])">&larr; Sebelumnya</x-link-button>
                    @else
                        <span></span>
                    @endif
                    @if ($next)
                        <x-link-button :href="route('courses.lessons.show', [$course, $next])">Selanjutnya &rarr;</x-link-button>
                    @endif
                </div>
            </div>

            {{-- Daftar lesson --}}
            <aside aria-labelledby="lesson-list-heading">
                <x-card class="overflow-hidden lg:sticky lg:top-6">
                    <h2 id="lesson-list-heading" class="border-b border-gray-100 px-5 py-4 font-semibold text-gray-900">Isi course</h2>
                    <ol class="max-h-[60vh] divide-y divide-gray-100 overflow-y-auto">
                        @foreach ($lessons as $item)
                            @php($active = $item->is($lesson))
                            <li>
                                <a href="{{ route('courses.lessons.show', [$course, $item]) }}" @if ($active) aria-current="page" @endif
                                   class="flex items-center gap-3 px-5 py-3 text-sm {{ $active ? 'bg-brand-50 font-semibold text-brand-800' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs {{ $active ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $loop->iteration }}</span>
                                    <span class="flex-1">{{ $item->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </x-card>
            </aside>
        </div>
    </div>
</x-app-layout>
