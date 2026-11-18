<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::withCount('lessons')
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.courses.index', compact('courses'));
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
            ->with('success', 'Course dibuat. Tambahkan lesson pertama.');
    }

    public function show(Course $course): View
    {
        $course->load('lessons');

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('admin.courses.show', $course)->with('success', 'Perubahan course disimpan.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course dihapus.');
    }
}
