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
                    :message="'Course “'.$course->title.'” beserta seluruh lesson dan videonya akan dihapus permanen.'"
                    label="Hapus course" />
            </x-slot>
        </x-page-header>

        <x-card class="flex flex-col gap-6 p-6 sm:flex-row">
            <x-course-thumbnail :course="$course" class="aspect-video w-full shrink-0 rounded-lg sm:w-56" />
            <div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500">
                    <x-badge :color="$course->status->color()">{{ $course->status->label() }}</x-badge>
                    <span>{{ $course->lessons->count() }} lesson</span>
                    @unless ($course->isPublished())
                        <span>Belum terlihat oleh student</span>
                    @endunless
                </div>
                <p class="mt-3 whitespace-pre-line text-gray-700">{{ $course->description }}</p>
            </div>
        </x-card>

        <section x-data="lessonSorter(@js(route('admin.courses.lessons.reorder', $course)))" aria-labelledby="lessons-heading">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="lessons-heading" class="text-lg font-semibold text-gray-900">Daftar lesson</h2>
                    <p class="text-sm text-gray-500">Seret <span aria-hidden="true">⠿</span> untuk mengubah urutan. Perubahan tersimpan otomatis.</p>
                </div>
                <x-link-button :href="route('admin.courses.lessons.create', $course)">+ Lesson baru</x-link-button>
            </div>

            <p x-show="message" x-cloak x-text="message" role="status" class="mb-3 text-sm font-medium text-emerald-700"></p>

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
                                    <x-badge :color="$lesson->video_status->color()">{{ $lesson->video_status->label() }}</x-badge>
                                    @if ($lesson->video_original_name)
                                        <span class="truncate text-xs text-gray-500">{{ $lesson->video_original_name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">Edit</a>
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
