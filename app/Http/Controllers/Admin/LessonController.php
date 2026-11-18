<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        // Lanjut ke halaman edit, tempat admin mengunggah video.
        return redirect()->route('admin.lessons.edit', $lesson)
            ->with('success', 'Lesson dibuat. Sekarang unggah videonya.');
    }

    public function edit(Lesson $lesson): View
    {
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
        $this->savePositions($course->lessons()->pluck('id')->all()); // rapatkan urutan

        return redirect()->route('admin.courses.show', $course)->with('success', 'Lesson dihapus.');
    }

    /** Dipanggil via fetch() saat admin selesai drag & drop lesson. */
    public function reorder(Request $request, Course $course): JsonResponse
    {
        $ids = $request->validate([
            // Harus berisi semua lesson milik course ini, tanpa duplikat.
            'lessons' => ['required', 'array', 'size:'.$course->lessons()->count()],
            'lessons.*' => ['integer', 'distinct', Rule::exists('lessons', 'id')->where('course_id', $course->id)],
        ])['lessons'];

        $this->savePositions($ids);

        return response()->json(['message' => 'Urutan lesson disimpan.']);
    }

    /** @param  array<int, int>  $ids  urutan id lesson, indeks 0 => posisi 1 */
    private function savePositions(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $index => $id) {
                Lesson::whereKey($id)->update(['position' => $index + 1]);
            }
        });
    }
}
