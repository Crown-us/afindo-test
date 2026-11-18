@props(['course'])

<a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
    <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-brand-600 to-brand-900">
        <x-application-logo class="h-12 w-12 opacity-90 transition group-hover:scale-110" />
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-700">{{ $course->title }}</h3>
        <p class="mt-2 line-clamp-3 flex-1 text-sm text-gray-600">{{ $course->description }}</p>
        <p class="mt-4 text-sm font-medium text-gray-500">{{ $course->lessons_count }} lesson</p>
    </div>
</a>
