<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\LessonVideoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Breeze mengarahkan ke sini setelah login/register; teruskan sesuai role.
Route::get('/dashboard', fn (Request $request) => redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'courses.index'))
    ->middleware('auth')
    ->name('dashboard');

// ---------- Area Admin ----------
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('courses', Admin\CourseController::class);

        // Lesson: create/store di bawah course, sisanya langsung by id (shallow).
        Route::patch('courses/{course}/lessons/reorder', [Admin\LessonController::class, 'reorder'])
            ->name('courses.lessons.reorder');
        Route::resource('courses.lessons', Admin\LessonController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->shallow();

        Route::post('lessons/{lesson}/video', [LessonVideoController::class, 'store'])->name('lessons.video.store');
    });

// Streaming video: admin & student, otorisasi di LessonPolicy.
Route::get('/lessons/{lesson}/video', [LessonVideoController::class, 'show'])
    ->middleware('auth')
    ->name('lessons.video');

// ---------- Area Student ----------
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::view('/courses', 'student.courses.index')->name('courses.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
