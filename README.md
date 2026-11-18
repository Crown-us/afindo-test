# Afindo Learn — Simple E-Learning Platform

Aplikasi e-learning sederhana berbasis **Laravel** untuk Technical Assessment Web Developer (Track A).
Admin mengelola course dan lesson berupa video; student menjelajahi katalog lalu menonton lesson secara berurutan.

![Beranda](docs/screenshots/beranda.jpg)

## Fitur

**Admin**
- Login dengan role admin; area `/admin` hanya untuk admin.
- Dashboard ringkas: jumlah course, lesson, video siap putar, dan lesson yang belum punya video.
- Course: lihat (cari & filter status), tambah, ubah, hapus. Data: judul, deskripsi, thumbnail, status publikasi (draft/published).
- Lesson: tambah, ubah, hapus, dan **urutkan dengan drag & drop** (tersimpan otomatis).
- **Upload video** lewat XHR dengan **progress bar**, validasi tipe & ukuran, dan status video `pending → uploading → ready / failed`.

**Guest & student**
- Beranda, katalog (dengan pencarian), dan detail course bisa dibuka tanpa login.
- Menonton lesson wajib login; setelah login otomatis kembali ke lesson yang dipilih.
- Player video dengan navigasi lesson sebelumnya/berikutnya dan daftar materi.
- Video disimpan di storage **private** dan hanya bisa diputar lewat route yang memeriksa otorisasi (course draft tidak bisa diakses student).

Detail rancangan (system design, ERD, flowchart/sequence, UI/UX, asumsi, keterbatasan): **[docs/DESIGN.md](docs/DESIGN.md)**.

## Teknologi

| Teknologi | Alasan singkat |
|---|---|
| Laravel 13, PHP 8.3+ | Diwajibkan brief; routing, validasi, Eloquent, policy, dan storage sudah tersedia. |
| MySQL 8 | Diwajibkan brief. (SQLite juga bisa untuk pengembangan lokal.) |
| Blade + Alpine.js | Render di server, interaksi kecil (upload, drag & drop, modal) cukup dengan Alpine; tidak perlu SPA. |
| Laravel Breeze | Scaffolding login/register/profil resmi Laravel. |
| Tailwind CSS | Desain konsisten lewat token warna & font di satu konfigurasi. |
| SortableJS | Drag & drop urutan lesson, ringan dan mendukung layar sentuh. |
| Pest | Feature test (63 test). |

## Kebutuhan

- PHP **8.3+** dengan ekstensi `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `gd`
- Composer 2
- Node.js **20.19+ atau 22.12+** dan npm
- MySQL 8 (atau MariaDB 10.6+)

## Instalasi

```bash
git clone <url-repo> afindo-learn
cd afindo-learn

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Buat database kosong, lalu sesuaikan `.env` bila perlu:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=afindo_elearning
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi + data demo, tautkan storage publik (untuk thumbnail), dan build aset:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

### Batas upload video

Upload video dibatasi oleh PHP. Untuk demo dengan video asli, naikkan nilai berikut di `php.ini` lalu restart server:

```ini
upload_max_filesize = 512M
post_max_size = 520M
```

Batas aplikasi sendiri adalah 500 MB. Halaman upload otomatis menampilkan batas yang berlaku (nilai terkecil antara batas aplikasi dan PHP).

## Menjalankan

```bash
composer run dev
```

Perintah ini menjalankan server Laravel dan Vite sekaligus. Buka http://localhost:8000.

Alternatif tanpa Vite dev server (setelah `npm run build`):

```bash
php artisan serve
```

## Akun demo

Semua password: **`password`**

| Role | Email |
|---|---|
| Admin | `admin@afindo.test` |
| Student | `student@afindo.test` |
| Student | `siti@afindo.test` |

Data demo berisi 4 course (3 published dengan video contoh di setiap lesson, 1 draft yang lesson-nya belum ada video untuk mencoba alur upload).

Untuk mengembalikan data ke kondisi awal:

```bash
php artisan migrate:fresh --seed
```

## Testing

```bash
php artisan test
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), jadi tidak menyentuh database lokal. Cakupan utama: akses per role, CRUD course & lesson, reorder, upload/ganti/hapus video & thumbnail, streaming video (termasuk HTTP Range), dan alur guest → login → kembali ke lesson.

## Struktur singkat

```
app/Enums/                    UserRole, CourseStatus, VideoStatus
app/Http/Controllers/Admin/   dashboard, course, lesson (+ reorder)
app/Http/Controllers/Student/ katalog, detail course, player lesson
app/Http/Controllers/LessonVideoController.php   upload & streaming video
app/Http/Middleware/EnsureUserHasRole.php        middleware role:admin
app/Policies/LessonPolicy.php                    siapa boleh menonton video
resources/js/                 video-uploader.js, lesson-sorter.js (Alpine)
resources/views/              admin/, student/, components/, layouts/
database/seeders/             akun demo, course, video & foto contoh
docs/DESIGN.md                dokumen desain
```

## Keterbatasan

Ringkasnya: belum ada progres belajar/enroll, video tidak di-transcode atau di-chunk (upload satu request), dan `nodownload` pada player bukan DRM. Daftar lengkap dan asumsi ada di [docs/DESIGN.md](docs/DESIGN.md#10-keterbatasan-dan-yang-belum-dikerjakan).

## Kredit

- Foto demo: [Unsplash](https://unsplash.com) (Unsplash License), rincian di `database/seeders/images/CREDITS.md`.
- Video contoh: dibuat sendiri dengan FFmpeg (`testsrc2`).
