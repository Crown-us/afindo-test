<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create();
});

test('katalog hanya menampilkan course yang sudah publish', function () {
    Course::factory()->published()->create(['title' => 'Course Terbit']);
    Course::factory()->create(['title' => 'Course Draft']);

    $this->actingAs($this->student)->get(route('courses.index'))
        ->assertOk()->assertSee('Course Terbit')->assertDontSee('Course Draft');
});

test('katalog bisa dicari berdasarkan judul', function () {
    Course::factory()->published()->create(['title' => 'Belajar Laravel']);
    Course::factory()->published()->create(['title' => 'Git Dasar']);

    $this->actingAs($this->student)->get(route('courses.index', ['q' => 'laravel']))
        ->assertOk()->assertSee('Belajar Laravel')->assertDontSee('Git Dasar');
});

test('detail course menampilkan lesson sesuai urutan', function () {
    $course = Course::factory()->published()->create();
    Lesson::factory()->for($course)->create(['title' => 'Lesson Kedua', 'position' => 2]);
    Lesson::factory()->for($course)->create(['title' => 'Lesson Pertama', 'position' => 1]);

    $this->actingAs($this->student)->get(route('courses.show', $course))
        ->assertOk()->assertSeeInOrder(['Lesson Pertama', 'Lesson Kedua']);
});

test('course draft dan lesson-nya tidak bisa dibuka student', function () {
    $lesson = Lesson::factory()->for(Course::factory())->create();

    $this->actingAs($this->student)->get(route('courses.show', $lesson->course))->assertNotFound();
    $this->actingAs($this->student)->get(route('courses.lessons.show', [$lesson->course, $lesson]))->assertNotFound();
});

test('halaman lesson menampilkan navigasi sebelumnya dan selanjutnya', function () {
    $course = Course::factory()->published()->create();
    [$first, $second, $third] = Lesson::factory()->for($course)->count(3)->create()->all();

    $this->actingAs($this->student)->get(route('courses.lessons.show', [$course, $second]))
        ->assertOk()
        ->assertSee('Lesson 2 dari 3')
        ->assertSee(route('courses.lessons.show', [$course, $first]))
        ->assertSee(route('courses.lessons.show', [$course, $third]));
});

test('guest melihat detail course, tapi membuka lesson diarahkan ke login lalu kembali ke lesson', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->for($course)->create(['title' => 'Lesson Publik']);
    $lessonUrl = route('courses.lessons.show', [$course, $lesson]);

    $this->get(route('courses.show', $course))->assertOk()->assertSee('Lesson Publik')->assertSee('Masuk untuk mulai belajar');
    $this->get($lessonUrl)->assertRedirect('/login');

    $this->post('/login', ['email' => $this->student->email, 'password' => 'password'])->assertRedirect($lessonUrl);
});

test('lesson dari course lain di URL menghasilkan 404', function () {
    $course = Course::factory()->published()->create();
    $otherLesson = Lesson::factory()->for(Course::factory()->published())->create();

    $this->actingAs($this->student)->get(route('courses.lessons.show', [$course, $otherLesson]))->assertNotFound();
});
