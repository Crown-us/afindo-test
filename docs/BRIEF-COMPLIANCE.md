# Matriks Kepatuhan Brief: Afindo Learn

Dokumen ini memetakan setiap poin pada file Technical Assessment Web Developer (Track A) ke implementasi di repo, bukti pengujian, dan dokumentasi pendukung.

Status yang dipakai:

- **Terpenuhi**: ada implementasi dan bukti uji.
- **Terpenuhi, catatan**: ada implementasi, tetapi ada hal yang perlu dijelaskan saat presentasi.
- **Tidak relevan**: poin brief tidak berlaku untuk track ini.

## 1. Informasi assessment

| Poin brief | Status | Bukti |
|---|---|---|
| Project: Simple E-Learning Platform | Terpenuhi | Aplikasi course dan lesson video di [README](../README.md) |
| Track: Web Developer | Terpenuhi | Area admin dan student, Laravel |
| Rekomendasi waktu 1-2 hari kerja | Terpenuhi | Pengerjaan 18-19 November 2026 |
| Level target Junior-Middle | Terpenuhi | Struktur Laravel standar, tanpa pola berlebihan |
| Design responsibility: system design, database/API, UI/UX, visual dibuat sendiri | Terpenuhi | [DESIGN.md](DESIGN.md), [PDF desain](Dokumen-Desain-Afindo-Learn.pdf), [file FigCraft](../design/afindo-learn.figcraft), [preview PDF](../design/afindo-learn-preview.pdf) |

## 2. Tujuan assessment

Brief menilai kemampuan memahami kebutuhan, merancang solusi, menulis kode terstruktur, dan menyelesaikan fitur wajib.

| Aspek | Status | Bukti |
|---|---|---|
| Memahami kebutuhan sederhana | Terpenuhi | Alur admin dan student mengikuti brief, tanpa fitur di luar cakupan |
| Merancang solusi | Terpenuhi | System design, ERD, flowchart, dan sequence diagram di [DESIGN.md](DESIGN.md) |
| Kode terstruktur | Terpenuhi | Controller tipis, FormRequest, Enum, Policy, dan test |
| Menyelesaikan fitur wajib | Terpenuhi | 89 test Pest dan 139 pemeriksaan QA lulus |

## 3. Deskripsi produk

| Poin brief | Status | Bukti |
|---|---|---|
| Admin mengelola course dan lesson berupa video | Terpenuhi | CRUD course, CRUD lesson, reorder, upload video |
| Student melihat course, membuka detail, memilih lesson, menonton video | Terpenuhi | Katalog, detail course, player lesson |
| Video hanya bisa ditonton sesuai akses | Terpenuhi | Disk private dan `LessonPolicy` |

## 4. Aturan umum

| Poin brief | Status | Bukti |
|---|---|---|
| Web Developer mengerjakan Track A | Terpenuhi | Area `/admin` dan area student |
| Bebas menentukan struktur, database, dan UI/UX, dengan ketentuan memakai Laravel | Terpenuhi | Laravel 13, MySQL 8, Blade, Tailwind |
| System design, ERD, API design (jika relevan), flowchart, dan UI/UX dirancang sendiri | Terpenuhi | [DESIGN.md](DESIGN.md) bagian 3, 5, 6, dan 8 |
| AI coding assistant dan library pihak ketiga boleh dipakai, kandidat harus memahami kode | Terpenuhi, catatan | Library dan alasannya ada di [README](../README.md#teknologi) dan [DESIGN.md](DESIGN.md#2-teknologi-dan-alasan). Pemahaman kode perlu dijelaskan sendiri saat interview |
| Data dummy relevan dan video contoh aman, tidak memakai karakter aneh | Terpenuhi | 4 course berbahasa Indonesia, video contoh FFmpeg, foto Unsplash dengan kredit di [CREDITS.md](../database/seeders/images/CREDITS.md) |
| Fitur tambahan opsional, dahulukan fitur wajib | Terpenuhi | Semua fitur wajib selesai, tambahan berupa thumbnail, pencarian, filter, drag and drop, dan progress upload |
| Asumsi dan batasan teknis ditulis di dokumentasi | Terpenuhi | [DESIGN.md](DESIGN.md#9-asumsi) dan [README](../README.md#keterbatasan) |
| Track A: aplikasi web sederhana dengan area Admin dan Student | Terpenuhi | Role admin dan student dengan middleware terpisah |

## 5. A1 Fitur wajib

| Fitur | Kriteria minimum brief | Implementasi | Bukti uji | Status |
|---|---|---|---|---|
| Authentication | Login untuk Admin dan Student, role/akses halaman dibedakan | Laravel Breeze, kolom `role`, middleware `role:admin`, register selalu student | [RoleAccessTest](../tests/Feature/RoleAccessTest.php), [AssessmentBriefValidationTest](../tests/Feature/AssessmentBriefValidationTest.php), QA B1/B2/B3/B5/E1/U11 | Terpenuhi |
| Course management | Admin dapat melihat, menambah, mengubah, menghapus course. Data minimum: judul, deskripsi, status publikasi | [CourseController](../app/Http/Controllers/Admin/CourseController.php), [CourseRequest](../app/Http/Requests/Admin/CourseRequest.php), halaman index, create, edit, show | [CourseManagementTest](../tests/Feature/Admin/CourseManagementTest.php), QA C1-C3/D1-D2/D10/U3 | Terpenuhi |
| Lesson management | Admin dapat menambah, mengubah, menghapus, dan mengurutkan lesson pada suatu course | [LessonController](../app/Http/Controllers/Admin/LessonController.php), reorder via PATCH, posisi otomatis | [LessonManagementTest](../tests/Feature/Admin/LessonManagementTest.php), QA D3/D9/U4/U6/U7 | Terpenuhi |
| Video upload | Admin dapat mengunggah file video untuk lesson | [LessonVideoController](../app/Http/Controllers/LessonVideoController.php), XHR dengan progress, validasi tipe dan ukuran, status video | [LessonVideoTest](../tests/Feature/LessonVideoTest.php), QA D4/D5/U5 | Terpenuhi |
| Student course view | Student dapat melihat daftar course dan detail course beserta lesson | Katalog, detail course, player lesson dengan daftar materi | [StudentCourseTest](../tests/Feature/StudentCourseTest.php), QA A2/E2/G2/U9/U10 | Terpenuhi |

## 6. A2 Batasan dan catatan teknis

| Poin brief | Status | Bukti |
|---|---|---|
| Tidak diwajibkan memakai Redis, queue worker, FFmpeg, transcoding, HLS, atau object storage | Terpenuhi | Tidak ada dependency tersebut, upload sinkron, disk lokal |
| Upload async di browser memakai AJAX, fetch, atau XHR, dengan progress | Terpenuhi | [video-uploader.js](../resources/js/video-uploader.js) memakai XHR dan event progress, QA U5 melihat progress 11 persen |
| Video disimpan di local atau private storage, akses mengikuti role dan otorisasi | Terpenuhi | Disk `local`, [LessonPolicy](../app/Policies/LessonPolicy.php), route streaming ber-otorisasi, QA F1/D6/D7/D8 |
| Minimal ada penanganan status video: uploading, ready, failed | Terpenuhi, catatan | Enum [VideoStatus](../app/Enums/VideoStatus.php) punya pending, uploading, ready, failed. Status failed sudah ada di kode, tetapi belum ada test otomatis untuk simulasi kegagalan penyimpanan |

## 7. A3 Teknologi

| Poin brief | Status | Bukti |
|---|---|---|
| Laravel, PHP, MySQL, Blade | Terpenuhi | `laravel/framework ^13.17`, PHP `^8.3`, MySQL 8, Blade |
| Vue atau React diperbolehkan | Tidak dipakai | Alpine.js dipilih untuk interaksi kecil, alasan ada di [DESIGN.md](DESIGN.md#2-teknologi-dan-alasan) |
| Wajib mencantumkan teknologi dan alasan singkat | Terpenuhi | Tabel teknologi di [README](../README.md#teknologi) dan [DESIGN.md](DESIGN.md#2-teknologi-dan-alasan) |

## 8. Desain system dan UI/UX

| Poin brief | Status | Bukti |
|---|---|---|
| System design sederhana: komponen utama dan alur data | Terpenuhi | [DESIGN.md](DESIGN.md#3-system-design) |
| Database design / ERD | Terpenuhi | [DESIGN.md](DESIGN.md#5-database--erd) dan folder migrations |
| Flowchart atau sequence diagram untuk pengelolaan course/lesson dan pemutaran video | Terpenuhi | [DESIGN.md](DESIGN.md#6-alur-utama): flowchart pengelolaan, sequence upload, sequence menonton |
| UI/UX untuk halaman yang benar-benar diimplementasikan | Terpenuhi | Screenshot halaman asli di [DESIGN.md](DESIGN.md#8-uiux), screenshot QA di [docs/qa](qa/TEST-PLAN.md) |
| Menentukan warna, tipografi, spacing, komponen, dan responsive behavior | Terpenuhi | Token desain dan aturan responsive di [DESIGN.md](DESIGN.md#8-uiux), dibuktikan QA U12-U14 |
| Dokumentasi desain tidak perlu panjang | Terpenuhi | Struktur dokumen ringkas per bagian |

## 9. Deliverables

| Deliverable | Status | Bukti |
|---|---|---|
| Source code di repository Git atau arsip yang bisa diperiksa, demo disiapkan | Terpenuhi | Repo GitHub: https://github.com/Crown-us/afindo-test, akun demo, [panduan demo](DEMO-GUIDE.md) |
| README: requirement, instalasi, konfigurasi, cara menjalankan, akun demo | Terpenuhi | [README](../README.md) |
| Dokumen/diagram system design, ERD atau model data, flowchart, catatan asumsi | Terpenuhi | [DESIGN.md](DESIGN.md), [PDF desain](Dokumen-Desain-Afindo-Learn.pdf) |
| Daftar keterbatasan atau bagian yang belum selesai | Terpenuhi | [README](../README.md#keterbatasan) dan [DESIGN.md](DESIGN.md#10-keterbatasan-dan-yang-belum-dikerjakan) |

## 10. Timeline

| Poin brief | Status | Bukti |
|---|---|---|
| Pengumpulan maksimal 3 hari setelah file assessment diberikan | Terpenuhi | Brief 18 November 2026, pekerjaan dan dokumentasi selesai 19 November 2026 |

## 11. Catatan untuk kandidat

| Poin brief | Status | Bukti |
|---|---|---|
| Implementasi fitur inti berjalan baik | Terpenuhi | 89 test Pest, 139 pemeriksaan QA |
| Struktur kode mudah dipahami | Terpenuhi | Controller tipis, FormRequest, Enum, Policy, dan penamaan konsisten |
| Keputusan teknis bisa dijelaskan | Terpenuhi | Alasan teknologi, aturan reorder, penyimpanan video, dan pembatasan akses ada di [DESIGN.md](DESIGN.md) |
| Solusi sederhana tetapi rapi | Terpenuhi | Tanpa Redis, queue, transcoding, atau SPA |

## 12. Catatan tambahan

- **API publik tidak ada.** Aplikasi memakai Blade. Dua endpoint JSON internal (upload video dan reorder lesson) didokumentasikan di [DESIGN.md](DESIGN.md#71-endpoint-json-internal).
- **PDF desain adalah snapshot.** Versi Markdown [DESIGN.md](DESIGN.md) adalah sumber terbaru. PDF dibuat sebelum beberapa catatan terakhir ditambahkan.
- **Demo online bersifat opsional.** Brief meminta demo disiapkan. Repo menyediakan data seed, akun demo, dan [panduan demo](DEMO-GUIDE.md) yang bisa dijalankan lokal dalam beberapa menit.
- **Pengujian QA** dijalankan di MySQL 8.4.9 dan SQLite in-memory, dengan Chromium untuk pengujian browser.
