<?php

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Fake disk dulu supaya seeder tidak menyentuh storage asli milik aplikasi.
    Storage::fake('public');
    Storage::fake('local');

    // Seed demo accounts and demo data
    $this->seed([UserSeeder::class, CourseSeeder::class]);

    $this->admin = User::where('email', 'admin@afindo.test')->first();
    $this->student = User::where('email', 'student@afindo.test')->first();
    $this->siti = User::where('email', 'siti@afindo.test')->first();
});

/*
|--------------------------------------------------------------------------
| A1.1 Authentication & Role-Based Access Control
|--------------------------------------------------------------------------
| Brief: Login untuk Admin dan Student. Role/akses halaman dibedakan.
*/
describe('A1.1 Authentication & Role-Based Access Control', function () {
    test('akun demo admin, student, dan siti terdaftar dengan role yang benar', function () {
        expect($this->admin)->not->toBeNull()
            ->and($this->admin->role)->toBe(UserRole::Admin)
            ->and($this->student)->not->toBeNull()
            ->and($this->student->role)->toBe(UserRole::Student)
            ->and($this->siti)->not->toBeNull()
            ->and($this->siti->role)->toBe(UserRole::Student);
    });

    test('admin bisa login dengan password demo dan diarahkan ke dashboard admin', function () {
        $response = $this->post('/login', [
            'email' => 'admin@afindo.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);

        // Akses /dashboard meneruskan admin ke /admin
        $this->actingAs($this->admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee('Dashboard');
    });

    test('student (Budi dan Siti) bisa login dengan password demo dan diarahkan ke katalog', function () {
        $response = $this->post('/login', [
            'email' => $this->student->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->student);
        $this->actingAs($this->student)->get('/dashboard')->assertRedirect(route('courses.index'));

        $this->post('/logout');

        $responseSiti = $this->post('/login', [
            'email' => $this->siti->email,
            'password' => 'password',
        ]);

        $responseSiti->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->siti);
        $this->actingAs($this->siti)->get('/dashboard')->assertRedirect(route('courses.index'));
    });

    test('student dan siti ditolak (403 Forbidden) saat mencoba mengakses area admin', function () {
        foreach ([$this->student, $this->siti] as $user) {
            $this->actingAs($user)->get('/admin')->assertForbidden();
            $this->actingAs($user)->get('/admin/courses')->assertForbidden();
            $this->actingAs($user)->get('/admin/courses/create')->assertForbidden();
        }
    });

    test('guest yang belum login diarahkan ke /login saat mengakses area admin atau dashboard', function () {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/courses')->assertRedirect('/login');
    });

    test('login dengan kredensial salah ditolak dengan error', function () {
        $this->post('/login', [
            'email' => 'admin@afindo.test',
            'password' => 'passwordsalah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });
});

/*
|--------------------------------------------------------------------------
| A1.2 Course Management
|--------------------------------------------------------------------------
| Brief: Admin dapat melihat, menambah, mengubah, dan menghapus course.
|        Data minimum: judul, deskripsi, dan status publikasi.
*/
describe('A1.2 Course Management (Admin)', function () {
    test('admin dapat melihat daftar course, mencari, dan memfilter status', function () {
        $this->actingAs($this->admin)->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('Kelola Course')
            ->assertSee('Dasar Pemrograman Web')
            ->assertSee('Membangun REST API');

        // Filter status
        $this->actingAs($this->admin)->get(route('admin.courses.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee('Membangun REST API')
            ->assertDontSee('Dasar Pemrograman Web');
    });

    test('admin dapat menambah course baru dengan judul, deskripsi, status, dan thumbnail', function () {
        $thumbnail = UploadedFile::fake()->image('laravel-docker.jpg', 800, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title' => 'Mastering Docker & K8s',
            'description' => 'Panduan lengkap kontainerisasi aplikasi dari nol sampai produksi.',
            'status' => CourseStatus::Published->value,
            'thumbnail' => $thumbnail,
        ]);

        $course = Course::firstWhere('title', 'Mastering Docker & K8s');
        expect($course)->not->toBeNull()
            ->and($course->status)->toBe(CourseStatus::Published)
            ->and($course->created_by)->toBe($this->admin->id);

        Storage::disk('public')->assertExists($course->thumbnail_path);
        $response->assertRedirect(route('admin.courses.show', $course));
    });

    test('validasi pembuatan course menolak field wajib yang kosong', function () {
        $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title' => '',
            'description' => '',
            'status' => 'invalid_status',
        ])->assertSessionHasErrors(['title', 'description', 'status']);
    });

    test('admin dapat mengubah data course dan mengganti thumbnail', function () {
        $course = Course::firstWhere('title', 'Membangun REST API dengan Laravel');
        $oldThumbnail = $course->thumbnail_path;

        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.png', 640, 480);

        $response = $this->actingAs($this->admin)->put(route('admin.courses.update', $course), [
            'title' => 'Membangun RESTful API Modern',
            'description' => 'Deskripsi yang sudah diperbarui dengan materi terbaru.',
            'status' => CourseStatus::Published->value,
            'thumbnail' => $newThumbnail,
        ]);

        $response->assertRedirect(route('admin.courses.show', $course));

        $course->refresh();
        expect($course->title)->toBe('Membangun RESTful API Modern')
            ->and($course->status)->toBe(CourseStatus::Published);

        // Pastikan thumbnail lama dihapus dan yang baru tersimpan
        if ($oldThumbnail) {
            Storage::disk('public')->assertMissing($oldThumbnail);
        }
        Storage::disk('public')->assertExists($course->thumbnail_path);
    });

    test('admin dapat menghapus course beserta cascade data dan storage-nya', function () {
        $course = Course::firstWhere('title', 'Membangun REST API dengan Laravel');
        $courseId = $course->id;

        $response = $this->actingAs($this->admin)->delete(route('admin.courses.destroy', $course));
        $response->assertRedirect(route('admin.courses.index'));

        expect(Course::find($courseId))->toBeNull()
            ->and(Lesson::where('course_id', $courseId)->count())->toBe(0);
    });
});

/*
|--------------------------------------------------------------------------
| A1.3 Lesson Management & Ordering
|--------------------------------------------------------------------------
| Brief: Admin dapat menambah, mengubah, menghapus, dan mengurutkan lesson pada suatu course.
*/
describe('A1.3 Lesson Management & Ordering', function () {
    test('admin dapat menambah lesson dan otomatis mendapatkan posisi urutan terakhir', function () {
        $course = Course::firstWhere('status', CourseStatus::Published);
        $initialCount = $course->lessons()->count();

        $response = $this->actingAs($this->admin)->post(route('admin.courses.lessons.store', $course), [
            'title' => 'Lesson Penutup dan Rangkuman',
            'description' => 'Rangkuman seluruh materi yang dipelajari.',
        ]);

        $lesson = Lesson::firstWhere('title', 'Lesson Penutup dan Rangkuman');
        expect($lesson)->not->toBeNull()
            ->and($lesson->course_id)->toBe($course->id)
            ->and($lesson->position)->toBe($initialCount + 1);

        $response->assertRedirect(route('admin.lessons.edit', $lesson));
    });

    test('admin dapat mengubah data lesson', function () {
        $lesson = Lesson::first();

        $response = $this->actingAs($this->admin)->put(route('admin.lessons.update', $lesson), [
            'title' => 'Judul Lesson Telah Direvisi',
            'description' => 'Deskripsi lesson yang telah disesuaikan.',
        ]);

        $response->assertRedirect(route('admin.courses.show', $lesson->course_id));
        expect($lesson->fresh()->title)->toBe('Judul Lesson Telah Direvisi');
    });

    test('admin dapat mengurutkan ulang (reorder) lesson pada suatu course', function () {
        $course = Course::firstWhere('status', CourseStatus::Published);
        $lessons = $course->lessons()->get();
        expect($lessons->count())->toBeGreaterThanOrEqual(3);

        $reversedIds = $lessons->pluck('id')->reverse()->values()->all();

        $response = $this->actingAs($this->admin)->patchJson(route('admin.courses.lessons.reorder', $course), [
            'lessons' => $reversedIds,
        ]);

        $response->assertOk()->assertJson(['message' => 'Urutan lesson disimpan.']);

        // Verifikasi urutan baru di database
        $newOrderIds = $course->fresh()->lessons()->pluck('id')->all();
        expect($newOrderIds)->toBe($reversedIds);
    });

    test('admin menghapus lesson akan merapatkan kembali nomor urutan posisi', function () {
        $course = Course::firstWhere('status', CourseStatus::Published);
        $lessons = $course->lessons()->get();
        $target = $lessons[1]; // Hapus lesson ke-2

        $this->actingAs($this->admin)->delete(route('admin.lessons.destroy', $target))
            ->assertRedirect(route('admin.courses.show', $course));

        $remainingPositions = $course->fresh()->lessons()->pluck('position')->all();
        // Harus berurutan 1, 2, 3...
        expect($remainingPositions)->toBe(range(1, count($remainingPositions)));
    });
});

/*
|--------------------------------------------------------------------------
| A1.4 Video Upload & Storage Security
|--------------------------------------------------------------------------
| Brief: Admin dapat mengunggah file video untuk lesson.
|        Upload async (AJAX/XHR) + progress bar.
|        Penyimpanan di local/private storage berotorisasi.
|        Penanganan status: uploading, ready, failed.
*/
describe('A1.4 Video Upload & Storage Security', function () {
    test('admin dapat mengunggah video lesson melalui API endpoint XHR', function () {
        $lesson = Lesson::where('video_status', VideoStatus::Pending)->first()
            ?? Lesson::factory()->create();

        $videoFile = UploadedFile::fake()->create('tutorial.mp4', 2048, 'video/mp4');

        $response = $this->actingAs($this->admin)->postJson(route('admin.lessons.video.store', $lesson), [
            'video' => $videoFile,
        ]);

        $response->assertOk()->assertJson(['message' => 'Video berhasil diunggah.']);

        $lesson->refresh();
        expect($lesson->video_status)->toBe(VideoStatus::Ready)
            ->and($lesson->video_original_name)->toBe('tutorial.mp4')
            ->and($lesson->video_mime)->toBe('video/mp4')
            ->and($lesson->video_path)->not->toBeNull();

        // Pastikan disimpan di disk private
        Storage::disk(Lesson::VIDEO_DISK)->assertExists($lesson->video_path);
    });

    test('unggah file non-video ditolak (422) dan status video tetap aman', function () {
        $lesson = Lesson::factory()->create(['video_status' => VideoStatus::Pending]);
        $fakeScript = UploadedFile::fake()->create('malicious.sh', 50, 'application/x-sh');

        $response = $this->actingAs($this->admin)->postJson(route('admin.lessons.video.store', $lesson), [
            'video' => $fakeScript,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('video');
        expect($lesson->fresh()->video_status)->toBe(VideoStatus::Pending);
    });

    test('student tidak memiliki akses mengunggah video (403 Forbidden)', function () {
        $lesson = Lesson::first();
        $videoFile = UploadedFile::fake()->create('test.mp4', 100, 'video/mp4');

        $this->actingAs($this->student)->postJson(route('admin.lessons.video.store', $lesson), [
            'video' => $videoFile,
        ])->assertForbidden();
    });
});

/*
|--------------------------------------------------------------------------
| A1.5 Student Course View, Authorization & Video Streaming
|--------------------------------------------------------------------------
| Brief: Student dapat melihat daftar course dan detail course beserta lesson.
|        Video playback dengan otorisasi & dukungan seek (HTTP Range).
*/
describe('A1.5 Student Course View & Video Streaming', function () {
    test('katalog menampilkan daftar course published dan menyembunyikan draft', function () {
        $this->actingAs($this->student)->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Dasar Pemrograman Web dengan Laravel')
            ->assertDontSee('Membangun REST API dengan Laravel'); // Course draft
    });

    test('pencarian katalog bekerja dengan baik', function () {
        $this->actingAs($this->student)->get(route('courses.index', ['q' => 'Git']))
            ->assertOk()
            ->assertSee('Git dan GitHub untuk Pemula')
            ->assertDontSee('Dasar Pemrograman Web');
    });

    test('student dapat melihat detail course published beserta materi lesson', function () {
        $course = Course::firstWhere('title', 'Dasar Pemrograman Web dengan Laravel');

        $this->actingAs($this->student)->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Dasar Pemrograman Web dengan Laravel')
            ->assertSee('Materi course');
    });

    test('student tidak dapat melihat detail course draft (404 Not Found)', function () {
        $draftCourse = Course::firstWhere('status', CourseStatus::Draft);

        $this->actingAs($this->student)->get(route('courses.show', $draftCourse))
            ->assertNotFound();
    });

    test('student dapat menonton lesson dari course published lengkap dengan navigasi', function () {
        $course = Course::firstWhere('title', 'Dasar Pemrograman Web dengan Laravel');
        $lessons = $course->lessons;
        $firstLesson = $lessons->first();

        $this->actingAs($this->student)->get(route('courses.lessons.show', [$course, $firstLesson]))
            ->assertOk()
            ->assertSee($firstLesson->title)
            ->assertSee('Materi course');
    });

    test('student dapat streaming video dari course published termasuk dukungan HTTP Range', function () {
        $course = Course::firstWhere('title', 'Dasar Pemrograman Web dengan Laravel');
        $lesson = $course->lessons()->where('video_status', VideoStatus::Ready)->first();

        // Buat mock file di storage local
        Storage::disk(Lesson::VIDEO_DISK)->put($lesson->video_path, 'mock-video-binary-content-stream');

        // Normal stream (200 OK)
        $this->actingAs($this->student)->get(route('lessons.video', $lesson))
            ->assertOk();

        // Range request untuk video seek (206 Partial Content)
        $this->actingAs($this->student)->get(route('lessons.video', $lesson), [
            'Range' => 'bytes=0-10',
        ])->assertStatus(206);
    });

    test('student ditolak (403 Forbidden) streaming video dari course draft', function () {
        $draftCourse = Course::firstWhere('status', CourseStatus::Draft);
        $draftLesson = $draftCourse->lessons()->first();

        $this->actingAs($this->student)->get(route('lessons.video', $draftLesson))
            ->assertForbidden();
    });

    test('guest yang membuka lesson diarahkan ke login lalu dikembalikan ke lesson tersebut (intended URL)', function () {
        $course = Course::firstWhere('title', 'Dasar Pemrograman Web dengan Laravel');
        $lesson = $course->lessons->first();
        $targetUrl = route('courses.lessons.show', [$course, $lesson]);

        // Guest akses lesson -> redirect login
        $this->get($targetUrl)->assertRedirect('/login');

        // Setelah login -> otomatis kembali ke lesson yang dituju
        $this->post('/login', [
            'email' => 'student@afindo.test',
            'password' => 'password',
        ])->assertRedirect($targetUrl);
    });
});
