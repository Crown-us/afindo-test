<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->course = Course::factory()->create();
});

test('admin menambah lesson lalu diarahkan ke halaman edit untuk unggah video', function () {
    $this->actingAs($this->admin)->get(route('admin.courses.lessons.create', $this->course))->assertOk();

    $response = $this->actingAs($this->admin)->post(route('admin.courses.lessons.store', $this->course), [
        'title' => 'Routing dan Controller',
        'description' => 'Membuat route pertama.',
    ]);

    $lesson = Lesson::firstWhere('title', 'Routing dan Controller');
    expect($lesson->course_id)->toBe($this->course->id)->and($lesson->position)->toBe(1);
    $response->assertRedirect(route('admin.lessons.edit', $lesson));

    $this->actingAs($this->admin)->get(route('admin.lessons.edit', $lesson))->assertOk()->assertSee('Routing dan Controller');
});

test('judul lesson wajib diisi', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.courses.lessons.store', $this->course), ['title' => ''])
        ->assertSessionHasErrors('title');
});

test('admin mengubah lesson', function () {
    $lesson = Lesson::factory()->for($this->course)->create();

    $this->actingAs($this->admin)->put(route('admin.lessons.update', $lesson), [
        'title' => 'Judul Lesson Baru',
        'description' => null,
    ])->assertRedirect(route('admin.courses.show', $this->course));

    expect($lesson->fresh()->title)->toBe('Judul Lesson Baru');
});

test('menghapus lesson menghapus file video dan merapatkan urutan', function () {
    Storage::fake('local');
    [$a, $b, $c] = Lesson::factory()->for($this->course)->count(3)->create()->all();
    $b->forceFill(['video_path' => $b->videoDirectory().'/v.mp4'])->save();
    Storage::disk('local')->put($b->video_path, 'dummy');

    $this->actingAs($this->admin)->delete(route('admin.lessons.destroy', $b))
        ->assertRedirect(route('admin.courses.show', $this->course));

    Storage::disk('local')->assertMissing($b->video_path);
    expect($this->course->lessons()->pluck('position', 'id')->all())->toBe([$a->id => 1, $c->id => 2]);
});

test('admin mengurutkan ulang lesson', function () {
    [$a, $b, $c] = Lesson::factory()->for($this->course)->count(3)->create()->all();

    $this->actingAs($this->admin)
        ->patchJson(route('admin.courses.lessons.reorder', $this->course), ['lessons' => [$c->id, $a->id, $b->id]])
        ->assertOk();

    expect($this->course->lessons()->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);
});

test('reorder ditolak jika daftar lesson tidak lengkap, duplikat, atau milik course lain', function () {
    [$a, $b] = Lesson::factory()->for($this->course)->count(2)->create()->all();
    $foreign = Lesson::factory()->create();

    foreach ([[$a->id], [$a->id, $a->id], [$a->id, $foreign->id], [$a->id, $b->id, $foreign->id]] as $payload) {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.courses.lessons.reorder', $this->course), ['lessons' => $payload])
            ->assertUnprocessable();
    }

    expect($this->course->lessons()->pluck('id')->all())->toBe([$a->id, $b->id]);
});

test('student tidak bisa mengelola lesson', function () {
    $student = User::factory()->create();
    $lesson = Lesson::factory()->for($this->course)->create();

    $this->actingAs($student)->post(route('admin.courses.lessons.store', $this->course), ['title' => 'X lesson'])->assertForbidden();
    $this->actingAs($student)->patchJson(route('admin.courses.lessons.reorder', $this->course), ['lessons' => [$lesson->id]])->assertForbidden();
    $this->actingAs($student)->delete(route('admin.lessons.destroy', $lesson))->assertForbidden();

    expect(Lesson::count())->toBe(1);
});
