<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Course $course, Lesson $lesson): View
    {
        abort_unless($course->isPublished(), 404);

        $lessons = $course->lessons;
        $index = $lessons->search(fn (Lesson $item) => $item->is($lesson));

        return view('student.lessons.show', [
            'course' => $course,
            'lesson' => $lesson,
            'lessons' => $lessons,
            'number' => $index + 1,
            'previous' => $lessons->get($index - 1),
            'next' => $lessons->get($index + 1),
        ]);
    }
}
