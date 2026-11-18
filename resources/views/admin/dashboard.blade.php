<x-app-layout title="Dashboard Admin">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
        <x-page-header title="Halo, {{ auth()->user()->name }}" subtitle="Ringkasan konten e-learning Anda.">
            <x-slot name="actions">
                <x-link-button :href="route('admin.courses.create')">+ Course baru</x-link-button>
            </x-slot>
        </x-page-header>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Course published', $stats['published'], 'text-emerald-600'],
                ['Course draft', $stats['draft'], 'text-gray-600'],
                ['Total lesson', $stats['lessons'], 'text-gray-900'],
                ['Video siap putar', $stats['videos_ready'].' / '.$stats['lessons'], 'text-brand-700'],
            ] as [$label, $value, $color])
                <x-card class="p-5">
                    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight {{ $color }}">{{ $value }}</p>
                </x-card>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <x-card class="lg:col-span-3">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Course terbaru</h2>
                    <a href="{{ route('admin.courses.index') }}" class="text-sm font-medium text-brand-700 hover:underline">Lihat semua</a>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($recentCourses as $course)
                        <li>
                            <a href="{{ route('admin.courses.show', $course) }}" class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-gray-50">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900">{{ $course->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $course->lessons_count }} lesson · diperbarui {{ $course->updated_at->diffForHumans() }}</p>
                                </div>
                                <x-course-status-badge :status="$course->status" />
                            </a>
                        </li>
                    @empty
                        <li><x-empty-state title="Belum ada course" description="Buat course pertama untuk mulai mengisi materi." /></li>
                    @endforelse
                </ul>
            </x-card>

            <x-card class="lg:col-span-2">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Lesson butuh video</h2>
                    <p class="text-xs text-gray-500">Lesson tanpa video tidak bisa diputar student.</p>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($needsVideo as $lesson)
                        <li>
                            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $lesson->title }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $lesson->course->title }}</p>
                                </div>
                                <x-video-status-badge :status="$lesson->video_status" />
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-gray-500">Semua lesson sudah memiliki video.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
</x-app-layout>
