<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Course & lesson dummy. Thumbnail course dari database/seeders/images (foto Unsplash, lihat CREDITS.md).
 * Lesson di course yang sudah publish diberi video contoh
 * (database/seeders/videos/sample.mp4, dibuat dengan FFmpeg testsrc); lesson di course
 * draft dibiarkan "pending" untuk demo alur upload.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@afindo.test')->value('id');

        foreach ($this->courses() as $data) {
            $course = new Course([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
            ]);
            $course->created_by = $adminId;
            $course->thumbnail_path = Storage::disk(Course::THUMBNAIL_DISK)
                ->putFile('thumbnails', database_path("seeders/images/{$data['thumbnail']}"));
            $course->save();

            foreach ($data['lessons'] as [$title, $description]) {
                $lesson = $course->lessons()->create(['title' => $title, 'description' => $description]);

                if ($course->isPublished()) {
                    $this->attachSampleVideo($lesson);
                }
            }
        }
    }

    private function attachSampleVideo(Lesson $lesson): void
    {
        $source = database_path('seeders/videos/sample.mp4');
        $path = "videos/course-{$lesson->course_id}/lesson-{$lesson->id}/sample.mp4";
        Storage::disk(Lesson::VIDEO_DISK)->put($path, file_get_contents($source));

        $lesson->forceFill([
            'video_path' => $path,
            'video_original_name' => 'sample.mp4',
            'video_mime' => 'video/mp4',
            'video_size' => filesize($source),
            'video_status' => VideoStatus::Ready,
        ])->save();
    }

    /** @return array<int, array<string, mixed>> */
    private function courses(): array
    {
        return [
            [
                'title' => 'Dasar Pemrograman Web dengan Laravel',
                'thumbnail' => 'laravel.jpg',
                'status' => CourseStatus::Published,
                'description' => 'Pelajari konsep inti Laravel mulai dari routing, controller, Blade, hingga Eloquent ORM. Cocok untuk developer PHP yang ingin membangun aplikasi web yang rapi dan mudah dikembangkan.',
                'lessons' => [
                    ['Pengenalan Laravel dan Struktur Proyek', 'Mengenal arsitektur MVC, struktur folder, dan cara menjalankan proyek Laravel pertama.'],
                    ['Routing dan Controller', 'Membuat route, route parameter, dan memindahkan logika ke controller.'],
                    ['Blade Template dan Layout', 'Menyusun tampilan dengan Blade, layout, component, dan slot.'],
                    ['Eloquent ORM dan Relasi', 'Mengelola data dengan model, migration, dan relasi one-to-many.'],
                ],
            ],
            [
                'title' => 'Git dan GitHub untuk Pemula',
                'thumbnail' => 'git.jpg',
                'status' => CourseStatus::Published,
                'description' => 'Kuasai version control dari nol: commit, branch, merge, hingga kolaborasi lewat pull request di GitHub.',
                'lessons' => [
                    ['Apa Itu Version Control?', 'Alasan tim developer membutuhkan version control dan cara kerja Git.'],
                    ['Commit dan Riwayat Perubahan', 'Membuat commit yang baik dan membaca riwayat dengan git log.'],
                    ['Branch dan Merge', 'Bekerja di branch terpisah lalu menggabungkannya dengan aman.'],
                ],
            ],
            [
                'title' => 'Desain UI/UX untuk Developer',
                'thumbnail' => 'uiux.jpg',
                'status' => CourseStatus::Published,
                'description' => 'Prinsip desain antarmuka yang praktis untuk developer: hierarki visual, tipografi, warna, dan aksesibilitas.',
                'lessons' => [
                    ['Hierarki Visual dan Tata Letak', 'Mengatur elemen agar informasi penting terlihat lebih dulu.'],
                    ['Tipografi dan Warna', 'Memilih font dan palet warna yang konsisten dan mudah dibaca.'],
                    ['Aksesibilitas Dasar', 'Kontras warna, ukuran target sentuh, dan navigasi keyboard.'],
                ],
            ],
            [
                'title' => 'Membangun REST API dengan Laravel',
                'thumbnail' => 'rest-api.jpg',
                'status' => CourseStatus::Draft,
                'description' => 'Merancang dan membangun REST API: resource controller, validasi, API resource, dan autentikasi token. (Masih dalam penyusunan.)',
                'lessons' => [
                    ['Prinsip Desain REST API', 'Resource, HTTP method, status code, dan konvensi penamaan endpoint.'],
                    ['Validasi dan API Resource', 'Memvalidasi input dan membentuk response JSON yang konsisten.'],
                ],
            ],
        ];
    }
}
