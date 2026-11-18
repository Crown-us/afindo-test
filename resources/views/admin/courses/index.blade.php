<x-app-layout title="Kelola Course">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
        <x-page-header title="Kelola Course" subtitle="Tambah, ubah, dan atur status publikasi course.">
            <x-slot name="actions">
                <x-link-button :href="route('admin.courses.create')">+ Course baru</x-link-button>
            </x-slot>
        </x-page-header>

        @php($filtered = filled($filters['q'] ?? null) || filled($filters['status'] ?? null))

        {{-- Filter --}}
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label for="q" class="sr-only">Cari course</label>
            <x-text-input id="q" name="q" type="search" placeholder="Cari judul course…" value="{{ $filters['q'] ?? '' }}" class="w-full sm:max-w-xs" />
            <label for="status" class="sr-only">Status</label>
            <x-select id="status" name="status" class="w-full sm:w-44" onchange="this.form.submit()">
                <option value="">Semua status</option>
                @foreach (\App\Enums\CourseStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-2">
                <x-secondary-button type="submit">Terapkan</x-secondary-button>
                @if ($filtered)
                    <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center px-3 text-sm font-medium text-gray-600 hover:text-gray-900">Reset</a>
                @endif
            </div>
        </form>

        <x-card class="overflow-hidden">
            @if ($courses->isEmpty())
                <x-empty-state
                    :title="$filtered ? 'Tidak ada course yang cocok' : 'Belum ada course'"
                    :description="$filtered ? 'Coba ubah kata kunci atau filter status.' : 'Mulai dengan membuat course pertama Anda.'">
                    @unless ($filtered)
                        <x-slot name="action">
                            <x-link-button :href="route('admin.courses.create')">+ Course baru</x-link-button>
                        </x-slot>
                    @endunless
                </x-empty-state>
            @else
                {{-- Tabel (desktop) --}}
                <table class="hidden min-w-full divide-y divide-gray-200 md:table">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th scope="col" class="px-5 py-3">Course</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3">Lesson</th>
                            <th scope="col" class="px-5 py-3">Diperbarui</th>
                            <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($courses as $course)
                            <tr class="hover:bg-gray-50">
                                <td class="max-w-md px-5 py-4">
                                    <a href="{{ route('admin.courses.show', $course) }}" class="font-semibold text-gray-900 hover:text-brand-700">{{ $course->title }}</a>
                                    <p class="mt-0.5 line-clamp-1 text-sm text-gray-500">{{ $course->description }}</p>
                                </td>
                                <td class="px-5 py-4"><x-course-status-badge :status="$course->status" /></td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $course->lessons_count }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500" title="{{ $course->updated_at->format('d M Y H:i') }}">{{ $course->updated_at->diffForHumans() }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @include('admin.courses.partials.row-actions', ['modalSuffix' => 'd'])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Kartu (mobile) --}}
                <ul class="divide-y divide-gray-100 md:hidden">
                    @foreach ($courses as $course)
                        <li class="space-y-2 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('admin.courses.show', $course) }}" class="font-semibold text-gray-900">{{ $course->title }}</a>
                                <x-course-status-badge :status="$course->status" />
                            </div>
                            <p class="text-xs text-gray-500">{{ $course->lessons_count }} lesson · diperbarui {{ $course->updated_at->diffForHumans() }}</p>
                            <div class="-ms-2.5">@include('admin.courses.partials.row-actions', ['modalSuffix' => 'm'])</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        {{ $courses->links() }}
    </div>
</x-app-layout>
