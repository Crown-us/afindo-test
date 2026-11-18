<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VideoStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'courseCount' => Course::count(),
            'lessonCount' => Lesson::count(),
            'readyCount' => Lesson::where('video_status', VideoStatus::Ready)->count(),
            'recentCourses' => Course::withCount('lessons')->latest('updated_at')->limit(5)->get(),
            // Lesson yang belum bisa diputar student karena videonya belum siap.
            'needsVideo' => Lesson::with('course')->where('video_status', '!=', VideoStatus::Ready)->latest()->limit(6)->get(),
        ]);
    }
}
