<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data course & lesson dummy yang relevan. Video belum dilampirkan di sini;
 * lesson berstatus "pending" sampai admin mengunggah video.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@afindo.test')->first();

        foreach ($this->courses() as $data) {
            $course = Course::firstOrNew(['title' => $data['title']]);
            $course->fill([
                'description' => $data['description'],
                'status' => $data['status'],
            ]);
            $course->created_by = $admin?->id;
            $course->save();

            if ($course->lessons()->exists()) {
                continue;
            }

            foreach ($data['lessons'] as [$title, $description]) {
                $course->lessons()->create([
                    'title' => $title,
                    'description' => $description,
                ]);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function courses(): array
    {
        return [
            [
                'title' => 'Dasar Pemrograman Web dengan Laravel',
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
