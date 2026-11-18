<x-app-layout title="Edit Lesson">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
        <x-page-header :title="$lesson->title" subtitle="Lesson #{{ $lesson->position }} di course ini.">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-gray-700 truncate max-w-[16rem]">{{ $course->title }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Edit lesson</span>
            </x-slot>
        </x-page-header>

        {{-- Video --}}
        <section aria-labelledby="video-heading" class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 id="video-heading" class="text-lg font-semibold text-gray-900">Video</h2>
                <x-badge :color="$lesson->video_status->color()">{{ $lesson->video_status->label() }}</x-badge>
            </div>
            <x-card class="p-6">
                {{-- Diisi pada tahap fitur upload video (XHR + progress). --}}
                <p class="text-sm text-gray-500">Fitur unggah video akan tersedia di sini.</p>
            </x-card>
        </section>

        {{-- Data lesson --}}
        <section aria-labelledby="detail-heading" class="space-y-3">
            <h2 id="detail-heading" class="text-lg font-semibold text-gray-900">Detail lesson</h2>
            @include('admin.lessons.partials.form', [
                'action' => route('admin.lessons.update', $lesson),
                'method' => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'cancelUrl' => route('admin.courses.show', $course),
            ])
        </section>
    </div>
</x-app-layout>
