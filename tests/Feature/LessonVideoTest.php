<?php

use App\Enums\CourseStatus;
use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->admin = User::factory()->admin()->create();
    $this->student = User::factory()->create();
});

function lessonWithVideo(CourseStatus $status): Lesson
{
    $lesson = Lesson::factory()->for(Course::factory()->state(['status' => $status]))->create();
    $path = "videos/course-{$lesson->course_id}/lesson-{$lesson->id}/video.mp4";
    Storage::disk('local')->put($path, 'isi-video');
    $lesson->forceFill(['video_path' => $path, 'video_status' => VideoStatus::Ready])->save();

    return $lesson;
}

test('admin mengunggah video dan status lesson menjadi ready', function () {
    $lesson = Lesson::factory()->create();
    $file = UploadedFile::fake()->create('materi.mp4', 1024, 'video/mp4');

    $this->actingAs($this->admin)
        ->postJson(route('admin.lessons.video.store', $lesson), ['video' => $file])
        ->assertOk();

    $lesson->refresh();
    expect($lesson->video_status)->toBe(VideoStatus::Ready)
        ->and($lesson->video_original_name)->toBe('materi.mp4')
        ->and($lesson->video_size)->toBe(1024 * 1024);
    Storage::disk('local')->assertExists($lesson->video_path);
});

test('mengganti video menghapus file lama', function () {
    $lesson = lessonWithVideo(CourseStatus::Published);
    $oldPath = $lesson->video_path;

    $this->actingAs($this->admin)
        ->postJson(route('admin.lessons.video.store', $lesson), ['video' => UploadedFile::fake()->create('baru.mp4', 10, 'video/mp4')])
        ->assertOk();

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($lesson->fresh()->video_path);
});

test('file selain video ditolak dan status lesson tidak berubah', function () {
    $lesson = Lesson::factory()->create();

    $this->actingAs($this->admin)
        ->postJson(route('admin.lessons.video.store', $lesson), ['video' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('video');

    expect($lesson->fresh()->video_status)->toBe(VideoStatus::Pending);
});

test('student tidak bisa mengunggah video', function () {
    $lesson = Lesson::factory()->create();

    $this->actingAs($this->student)
        ->postJson(route('admin.lessons.video.store', $lesson), ['video' => UploadedFile::fake()->create('a.mp4', 10, 'video/mp4')])
        ->assertForbidden();
});

test('student bisa memutar video dari course yang sudah publish', function () {
    $lesson = lessonWithVideo(CourseStatus::Published);

    $this->actingAs($this->student)->get(route('lessons.video', $lesson))->assertOk();

    // Mendukung Range request agar video bisa di-seek.
    $this->actingAs($this->student)
        ->get(route('lessons.video', $lesson), ['Range' => 'bytes=0-3'])
        ->assertStatus(206);
});

test('student tidak bisa memutar video dari course draft, admin bisa', function () {
    $lesson = lessonWithVideo(CourseStatus::Draft);

    $this->actingAs($this->student)->get(route('lessons.video', $lesson))->assertForbidden();
    $this->actingAs($this->admin)->get(route('lessons.video', $lesson))->assertOk();
});

test('guest tidak bisa memutar video', function () {
    $lesson = lessonWithVideo(CourseStatus::Published);

    $this->get(route('lessons.video', $lesson))->assertRedirect('/login');
});

test('video yang belum siap mengembalikan 404', function () {
    $lesson = Lesson::factory()->for(Course::factory()->published())->create();

    $this->actingAs($this->student)->get(route('lessons.video', $lesson))->assertNotFound();
});
