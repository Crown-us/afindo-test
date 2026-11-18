<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('admin melihat daftar course dengan pencarian dan filter status', function () {
    Course::factory()->published()->create(['title' => 'Belajar Laravel']);
    Course::factory()->draft()->create(['title' => 'Belajar Vue']);
    Course::factory()->published()->create(['title' => 'Git Dasar']);

    $this->actingAs($this->admin)->get(route('admin.courses.index'))
        ->assertOk()->assertSee('Belajar Laravel')->assertSee('Belajar Vue')->assertSee('Git Dasar');

    $this->actingAs($this->admin)->get(route('admin.courses.index', ['q' => 'Belajar', 'status' => 'published']))
        ->assertOk()->assertSee('Belajar Laravel')->assertDontSee('Belajar Vue')->assertDontSee('Git Dasar');
});

test('admin dashboard tampil dengan statistik', function () {
    Course::factory()->published()->has(Lesson::factory()->count(2))->create();

    $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Lesson butuh video');
});

test('admin bisa membuat course', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
        'title' => '  Pengenalan Docker  ',
        'description' => 'Belajar container dari dasar sampai deploy.',
        'status' => 'published',
    ]);

    $course = Course::firstWhere('title', 'Pengenalan Docker');
    expect($course)->not->toBeNull()
        ->and($course->title)->toBe('Pengenalan Docker')
        ->and($course->status)->toBe(CourseStatus::Published)
        ->and($course->created_by)->toBe($this->admin->id);

    $response->assertRedirect(route('admin.courses.show', $course))->assertSessionHas('success');
});

test('validasi course menolak input kosong dan status tidak dikenal', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.courses.store'), ['title' => '', 'description' => 'pendek', 'status' => 'archived'])
        ->assertSessionHasErrors(['title', 'description', 'status']);

    expect(Course::count())->toBe(0);
});

test('admin bisa mengubah course dan halaman edit/detail tampil', function () {
    $course = Course::factory()->draft()->create();
    Lesson::factory()->for($course)->create(['title' => 'Lesson Pertama']);

    $this->actingAs($this->admin)->get(route('admin.courses.show', $course))->assertOk()->assertSee('Lesson Pertama');
    $this->actingAs($this->admin)->get(route('admin.courses.edit', $course))->assertOk();

    $this->actingAs($this->admin)->put(route('admin.courses.update', $course), [
        'title' => 'Judul Baru',
        'description' => 'Deskripsi baru yang cukup panjang.',
        'status' => 'published',
    ])->assertRedirect(route('admin.courses.show', $course));

    $course->refresh();
    expect($course->title)->toBe('Judul Baru')
        ->and($course->isPublished())->toBeTrue();
});

test('menghapus course ikut menghapus lesson dan folder videonya', function () {
    Storage::fake('local');
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create();
    $path = "videos/course-{$course->id}/lesson-{$lesson->id}/video.mp4";
    Storage::disk('local')->put($path, 'dummy');

    $this->actingAs($this->admin)->delete(route('admin.courses.destroy', $course))
        ->assertRedirect(route('admin.courses.index'));

    expect(Course::count())->toBe(0)->and(Lesson::count())->toBe(0);
    Storage::disk('local')->assertMissing($path);
});

test('student tidak bisa mengakses satupun aksi manajemen course', function () {
    $student = User::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($student)->get(route('admin.courses.index'))->assertForbidden();
    $this->actingAs($student)->post(route('admin.courses.store'), [
        'title' => 'Hack', 'description' => 'Mencoba membuat course.', 'status' => 'published',
    ])->assertForbidden();
    $this->actingAs($student)->delete(route('admin.courses.destroy', $course))->assertForbidden();

    expect(Course::count())->toBe(1);
});
