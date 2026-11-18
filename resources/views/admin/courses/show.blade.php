<x-app-layout :title="$course->title">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
        <x-page-header :title="$course->title">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Detail</span>
            </x-slot>
            <x-slot name="actions">
                <x-link-button variant="secondary" :href="route('admin.courses.edit', $course)">Edit course</x-link-button>
                <x-confirm-delete
                    :action="route('admin.courses.destroy', $course)"
                    name="hapus-course"
                    title="Hapus course ini?"
                    :message="'Course “'.$course->title.'” beserta '.$course->lessons_count.' lesson dan seluruh videonya akan dihapus permanen.'"
                    label="Hapus course" />
            </x-slot>
        </x-page-header>

        <x-card class="p-6">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500">
                <x-course-status-badge :status="$course->status" />
                <span>{{ $course->lessons_count }} lesson</span>
                @if ($course->published_at)
                    <span>Dipublish {{ $course->published_at->translatedFormat('d M Y') }}</span>
                @else
                    <span>Belum dipublish, tidak terlihat oleh student</span>
                @endif
            </div>
            <p class="mt-4 whitespace-pre-line text-gray-700">{{ $course->description }}</p>
        </x-card>

        <section x-data="lessonSorter({ url: @js(route('admin.courses.lessons.reorder', $course)) })" aria-labelledby="lessons-heading">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="lessons-heading" class="text-lg font-semibold text-gray-900">Daftar lesson</h2>
                    <p class="text-sm text-gray-500">Seret <span aria-hidden="true">⠿</span> atau gunakan tombol panah untuk mengubah urutan. Perubahan tersimpan otomatis.</p>
                </div>
                <x-link-button :href="route('admin.courses.lessons.create', $course)">+ Lesson baru</x-link-button>
            </div>

            {{-- Status penyimpanan urutan (dibacakan screen reader) --}}
            <p x-show="state !== 'idle'" x-cloak role="status" aria-live="polite"
               class="mb-3 text-sm font-medium"
               :class="{ 'text-gray-500': state === 'saving', 'text-emerald-700': state === 'saved', 'text-red-600': state === 'error' }"
               x-text="message"></p>

            <x-card class="overflow-hidden">
                @if ($course->lessons->isEmpty())
                    <x-empty-state title="Belum ada lesson" description="Tambahkan lesson lalu unggah video materinya.">
                        <x-slot name="action">
                            <x-link-button :href="route('admin.courses.lessons.create', $course)">+ Lesson baru</x-link-button>
                        </x-slot>
                    </x-empty-state>
                @endif

                <ol x-ref="list" class="divide-y divide-gray-100">
                    @foreach ($course->lessons as $lesson)
                        <li data-id="{{ $lesson->id }}" class="flex items-center gap-3 bg-white px-3 py-3 sm:px-4">
                            <button type="button" data-handle class="cursor-grab touch-none rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 active:cursor-grabbing" aria-label="Seret untuk mengubah urutan" tabindex="-1">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM16 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM16 16a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/></svg>
                            </button>

                            <span data-position class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ $loop->iteration }}</span>

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="block truncate font-medium text-gray-900 hover:text-brand-700">{{ $lesson->title }}</a>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <x-video-status-badge :status="$lesson->video_status" />
                                    @if ($lesson->video_original_name)
                                        <span class="truncate text-xs text-gray-500">{{ $lesson->video_original_name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <div class="flex flex-col sm:flex-row">
                                    <button type="button" @click="move($el, -1)" :disabled="state === 'saving'" class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-40" aria-label="Naikkan {{ $lesson->title }}">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <button type="button" @click="move($el, 1)" :disabled="state === 'saving'" class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-40" aria-label="Turunkan {{ $lesson->title }}">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="hidden rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 sm:inline-flex">Edit</a>
                                <x-confirm-delete
                                    :action="route('admin.lessons.destroy', $lesson)"
                                    :name="'hapus-lesson-'.$lesson->id"
                                    title="Hapus lesson ini?"
                                    :message="'Lesson “'.$lesson->title.'” dan videonya akan dihapus permanen.'" />
                            </div>
                        </li>
                    @endforeach
                </ol>
            </x-card>
        </section>
    </div>
</x-app-layout>
