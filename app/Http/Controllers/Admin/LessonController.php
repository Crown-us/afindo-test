<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LessonRequest;
use App\Http\Requests\Admin\ReorderLessonsRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function create(Course $course): View
    {
        return view('admin.lessons.create', ['course' => $course, 'lesson' => new Lesson]);
    }

    public function store(LessonRequest $request, Course $course): RedirectResponse
    {
        $lesson = $course->lessons()->create($request->validated());

        // Lanjut ke halaman edit, tempat admin mengunggah video lesson ini.
        return redirect()->route('admin.lessons.edit', $lesson)
            ->with('success', 'Lesson dibuat. Sekarang unggah videonya.');
    }

    public function edit(Lesson $lesson): View
    {
        $lesson->load('course');

        return view('admin.lessons.edit', ['lesson' => $lesson, 'course' => $lesson->course]);
    }

    public function update(LessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());

        return redirect()->route('admin.courses.show', $lesson->course_id)
            ->with('success', 'Perubahan lesson disimpan.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $course = $lesson->course;
        $lesson->delete();
        $course->normalizeLessonPositions();

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Lesson dihapus.');
    }

    /** Dipanggil via fetch() dari halaman detail course (drag & drop / tombol naik-turun). */
    public function reorder(ReorderLessonsRequest $request, Course $course): JsonResponse
    {
        $course->reorderLessons($request->validated('lessons'));

        return response()->json(['message' => 'Urutan lesson disimpan.']);
    }
}
