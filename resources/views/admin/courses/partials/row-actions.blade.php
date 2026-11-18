<div class="inline-flex flex-wrap items-center gap-1">
    <a href="{{ route('admin.courses.show', $course) }}" class="rounded-md px-2.5 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50">Kelola lesson</a>
    <a href="{{ route('admin.courses.edit', $course) }}" class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">Edit</a>
    <x-confirm-delete
        :action="route('admin.courses.destroy', $course)"
        :name="'hapus-course-'.$course->id.'-'.($modalSuffix ?? 'x')"
        title="Hapus course ini?"
        :message="'Course “'.$course->title.'” beserta '.$course->lessons_count.' lesson dan seluruh videonya akan dihapus permanen.'" />
</div>
