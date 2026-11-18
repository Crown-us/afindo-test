<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return $request->user()
        ? redirect($request->user()->homeRoute())
        : view('welcome');
})->name('home');

// Titik masuk setelah login/register (dipakai Breeze). Diarahkan ke halaman awal sesuai role.
Route::get('/dashboard', fn (Request $request) => redirect($request->user()->homeRoute()))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ---------- Area Admin ----------
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
    });

// ---------- Area Student ----------
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::view('/courses', 'student.courses.index')->name('courses.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
