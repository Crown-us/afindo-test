<x-app-layout :title="$course->title">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header :title="$course->title" subtitle="{{ $course->lessons->count() }} lesson">
            <x-slot name="breadcrumb">
                <a href="{{ route('courses.index') }}" class="hover:text-gray-700">Katalog Course</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Detail</span>
            </x-slot>
            @if ($course->lessons->isNotEmpty())
                <x-slot name="actions">
                    <x-link-button :href="route('courses.lessons.show', [$course, $course->lessons->first()])">Mulai belajar</x-link-button>
                </x-slot>
            @endif
        </x-page-header>

        <x-card class="p-6">
            <h2 class="font-semibold text-gray-900">Tentang course ini</h2>
            <p class="mt-2 whitespace-pre-line text-gray-700">{{ $course->description }}</p>
        </x-card>

        <section aria-labelledby="lessons-heading" class="space-y-3">
            <h2 id="lessons-heading" class="text-lg font-semibold text-gray-900">Daftar lesson</h2>
            <x-card class="overflow-hidden">
                <ol class="divide-y divide-gray-100">
                    @forelse ($course->lessons as $lesson)
                        <li>
                            <a href="{{ route('courses.lessons.show', [$course, $lesson]) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ $loop->iteration }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900">{{ $lesson->title }}</p>
                                    @if ($lesson->description)
                                        <p class="mt-0.5 line-clamp-1 text-sm text-gray-500">{{ $lesson->description }}</p>
                                    @endif
                                </div>
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
