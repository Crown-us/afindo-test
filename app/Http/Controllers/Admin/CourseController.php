<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(CourseStatus::class)],
        ]);

        $courses = Course::query()
            ->withCount('lessons')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.courses.index', compact('courses', 'filters'));
    }

    public function create(): View
    {
        return view('admin.courses.create', ['course' => new Course]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $course = new Course($request->validated());
        $course->created_by = $request->user()->id;
        $course->save();

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course berhasil dibuat. Tambahkan lesson pertama.');
    }

    public function show(Course $course): View
    {
        $course->load('lessons')->loadCount('lessons');

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Perubahan course disimpan.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', "Course \"{$title}\" beserta lesson dan videonya telah dihapus.");
    }
}
