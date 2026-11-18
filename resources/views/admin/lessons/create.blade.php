<x-app-layout title="Lesson Baru">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
        <x-page-header title="Lesson baru" subtitle="Simpan dulu data lesson, lalu unggah videonya di langkah berikutnya.">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-gray-700 truncate max-w-[16rem]">{{ $course->title }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Lesson baru</span>
            </x-slot>
        </x-page-header>

        @include('admin.lessons.partials.form', [
            'action' => route('admin.courses.lessons.store', $course),
            'method' => 'POST',
            'submitLabel' => 'Simpan & lanjut unggah video',
            'cancelUrl' => route('admin.courses.show', $course),
        ])
    </div>
</x-app-layout>
