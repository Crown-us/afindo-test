<x-app-layout title="Course Baru">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
        <x-page-header title="Course baru" subtitle="Isi informasi dasar course. Lesson ditambahkan setelah course disimpan.">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Baru</span>
            </x-slot>
        </x-page-header>

        @include('admin.courses.partials.form', [
            'action' => route('admin.courses.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan course',
        ])
    </div>
</x-app-layout>
