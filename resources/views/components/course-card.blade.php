@props(['course'])

<a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
    <div class="overflow-hidden">
        <x-course-thumbnail :course="$course" class="aspect-video w-full transition duration-300 group-hover:scale-[1.03]" />
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-semibold leading-snug text-gray-900">{{ $course->title }}</h3>
        <p class="mt-2 line-clamp-2 flex-1 text-sm text-gray-500">{{ $course->description }}</p>
        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
            <span class="inline-flex items-center gap-1.5 text-gray-600">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.25 4A2.25 2.25 0 0 0 1 6.25v7.5A2.25 2.25 0 0 0 3.25 16h7.5A2.25 2.25 0 0 0 13 13.75v-7.5A2.25 2.25 0 0 0 10.75 4h-7.5ZM19 4.75a.75.75 0 0 0-1.28-.53l-3 3a.75.75 0 0 0-.22.53v4.5c0 .2.08.39.22.53l3 3a.75.75 0 0 0 1.28-.53V4.75Z"/></svg>
                {{ $course->lessons_count }} video lesson
            </span>
            <span class="font-medium text-brand-600 group-hover:text-brand-700">Lihat course</span>
        </div>
    </div>
</a>
