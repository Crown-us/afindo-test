<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Enums\VideoStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'published' => Course::where('status', CourseStatus::Published)->count(),
            'draft' => Course::where('status', CourseStatus::Draft)->count(),
            'lessons' => Lesson::count(),
            'videos_ready' => Lesson::where('video_status', VideoStatus::Ready)->count(),
        ];

        // Lesson yang masih butuh perhatian admin (belum ada video / gagal upload).
        $needsVideo = Lesson::with('course:id,title')
            ->whereIn('video_status', [VideoStatus::Pending, VideoStatus::Failed, VideoStatus::Uploading])
            ->orderByRaw("CASE WHEN video_status = 'failed' THEN 0 ELSE 1 END")
            ->latest('updated_at')
            ->limit(6)
            ->get();

        $recentCourses = Course::withCount('lessons')->latest('updated_at')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'needsVideo', 'recentCourses'));
    }
}
