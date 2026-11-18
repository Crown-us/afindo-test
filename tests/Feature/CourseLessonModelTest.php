<?php

use App\Models\Course;
use App\Models\Lesson;

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
