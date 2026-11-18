<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonVideoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------- Publik (guest & user login) ----------
Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [Student\CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [Student\CourseController::class, 'show'])->name('courses.show');

// Menonton lesson wajib login; setelah login user kembali ke lesson ini (redirect intended).
// scopeBindings: lesson harus milik course di URL, selain itu 404.
Route::get('/courses/{course}/lessons/{lesson}', [Student\LessonController::class, 'show'])
    ->middleware('auth')
    ->scopeBindings()
    ->name('courses.lessons.show');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
