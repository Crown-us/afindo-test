<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $courses = Course::where('status', CourseStatus::Published)
            ->withCount('lessons')
            ->latest()
            ->limit(3)
            ->get();

        return view('home', compact('courses'));
    }
}
