<?php

namespace App\Http\Controllers\Student;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::where('status', CourseStatus::Published)
            ->withCount('lessons')
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('student.courses.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        // Course draft dianggap tidak ada bagi student.
        abort_unless($course->isPublished(), 404);

        $course->load('lessons');

        return view('student.courses.show', compact('course'));
    }
}
