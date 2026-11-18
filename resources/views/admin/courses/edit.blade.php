<x-app-layout title="Edit Course">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
        <x-page-header title="Edit course">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-gray-700 truncate max-w-[16rem]">{{ $course->title }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Edit</span>
            </x-slot>
        </x-page-header>

        @include('admin.courses.partials.form', [
            'action' => route('admin.courses.update', $course),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </div>
</x-app-layout>
