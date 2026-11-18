<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $published = Course::where('status', CourseStatus::Published);

        return view('home', [
            'courses' => (clone $published)->withCount('lessons')->latest()->limit(3)->get(),
            'courseCount' => (clone $published)->count(),
            'lessonCount' => Lesson::whereIn('course_id', (clone $published)->select('id'))->count(),
        ]);
    }
}
