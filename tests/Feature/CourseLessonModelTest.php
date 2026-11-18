<?php

use App\Enums\CourseStatus;
use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\Lesson;

test('slug course dibuat otomatis dan unik', function () {
    $a = Course::factory()->create(['title' => 'Belajar Laravel']);
    $b = Course::factory()->create(['title' => 'Belajar Laravel']);

    expect($a->slug)->toBe('belajar-laravel')
        ->and($b->slug)->toBe('belajar-laravel-2');
});

test('published_at terisi saat dipublish dan dikosongkan saat kembali draft', function () {
    $course = Course::factory()->draft()->create();
    expect($course->published_at)->toBeNull();

    $course->update(['status' => CourseStatus::Published]);
    expect($course->published_at)->not->toBeNull();

    $course->update(['status' => CourseStatus::Draft]);
    expect($course->published_at)->toBeNull();
});

test('scope published hanya mengambil course yang dipublish', function () {
    Course::factory()->published()->count(2)->create();
    Course::factory()->draft()->create();

    expect(Course::published()->count())->toBe(2);
});

test('lesson baru otomatis ditaruh di posisi terakhir per course', function () {
    $course = Course::factory()->create();
    $other = Course::factory()->create();

    $first = Lesson::factory()->for($course)->create();
    $second = Lesson::factory()->for($course)->create();
    $otherFirst = Lesson::factory()->for($other)->create();

    expect([$first->position, $second->position, $otherFirst->position])->toBe([1, 2, 1]);
});

test('relasi lessons selalu terurut berdasarkan position', function () {
    $course = Course::factory()->create();
    Lesson::factory()->for($course)->create(['title' => 'C', 'position' => 3]);
    Lesson::factory()->for($course)->create(['title' => 'A', 'position' => 1]);
    Lesson::factory()->for($course)->create(['title' => 'B', 'position' => 2]);

    expect($course->lessons->pluck('title')->all())->toBe(['A', 'B', 'C']);
});

test('menghapus course ikut menghapus lesson-nya', function () {
    $course = Course::factory()->create();
    Lesson::factory()->for($course)->count(2)->create();

    $course->delete();

    expect(Lesson::count())->toBe(0);
});

test('hanya video berstatus ready yang dianggap bisa diputar', function () {
    expect(Lesson::factory()->make()->hasPlayableVideo())->toBeFalse()
        ->and(Lesson::factory()->failed()->make()->hasPlayableVideo())->toBeFalse()
        ->and(Lesson::factory()->withVideo()->make()->hasPlayableVideo())->toBeTrue()
        ->and(Lesson::factory()->make()->video_status)->toBe(VideoStatus::Pending);
});
