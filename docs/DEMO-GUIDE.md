# Panduan Demo: Afindo Learn

Panduan ini dipakai untuk mendemokan aplikasi dalam 5 sampai 10 menit, langsung dari laptop. Tidak perlu server online.

## 1. Persiapan

Prasyarat:

- PHP 8.3 atau lebih baru
- Composer 2
- Node.js 20.19 atau 22.12 dan npm
- MySQL 8 yang sedang berjalan
- Browser modern

Langkah menjalankan dari nol:

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Sesuaikan database di `.env`, lalu:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka http://localhost:8000.

Kalau database dan build sudah siap, cukup jalankan:

```bash
php artisan serve
```

## 2. Akun demo

Semua password: `password`

| Role | Email |
|---|---|
| Admin | `admin@afindo.test` |
| Student | `student@afindo.test` |
| Student | `siti@afindo.test` |

Data awal berisi 4 course dan 12 lesson:

- 3 course published dengan video.
- 1 course draft tanpa video, untuk mendemokan upload.

## 3. Alur demo yang disarankan

### 0.00 - 1.00: tampilan publik

1. Buka beranda. Tunjukkan hero, statistik singkat, dan daftar course terbaru.
2. Buka katalog. Coba pencarian, misalnya `Git`.
3. Buka detail salah satu course. Tunjukkan deskripsi, daftar lesson, dan tombol mulai belajar.
4. Buka course draft lewat URL langsung, misalnya `/courses/4`. Hasilnya 404, membuktikan draft tidak bocor ke publik.

### 1.00 - 2.00: login admin

1. Klik Masuk, gunakan `admin@afindo.test` dan `password`.
2. Admin diarahkan ke dashboard `/admin`.
3. Tunjukkan statistik: 4 course, 12 lesson, 10 dari 12 video siap.
4. Buka Kelola Course. Tunjukkan pencarian, filter status, dan paginasi.

### 2.00 - 4.30: kelola course dan lesson

1. Klik Course baru.
2. Isi judul, deskripsi, pilih status published, dan unggah thumbnail.
3. Simpan. Perhatikan notifikasi dan halaman detail course.
4. Klik Lesson baru, isi judul, lalu simpan. Admin langsung diarahkan ke halaman edit lesson.
5. Unggah video contoh, misalnya `database/seeders/videos/sample.mp4`.
6. Tunjukkan progress bar berjalan, lalu badge berubah menjadi Siap dan preview video muncul.
7. Kembali ke detail course. Tambah satu lesson lagi, lalu drag and drop untuk mengubah urutan. Reload halaman untuk membuktikan urutan tersimpan.
8. Buka katalog publik atau detail course sebagai guest untuk menunjukkan course published sudah tampil.

Catatan upload: batas bawaan PHP biasanya 2 MB. Untuk file lebih besar, jalankan server dengan batas yang lebih tinggi seperti di [README](../README.md#batas-upload-video).

### 4.30 - 6.00: alur student

1. Keluar dari akun admin, atau buka jendela incognito.
2. Buka detail course, klik Mulai belajar. Guest diarahkan ke login.
3. Login sebagai `student@afindo.test`.
4. Setelah login, user kembali ke lesson yang tadi dipilih.
5. Tunjukkan player, daftar materi, tombol lesson sebelumnya dan berikutnya.
6. Putar video dan geser timeline untuk membuktikan dukungan HTTP Range.
7. Buka `/admin` sebagai student. Hasilnya 403.

### 6.00 - 7.00: bukti keamanan dan pengujian

1. Buka `http://localhost:8000/storage/videos/course-1/lesson-1/sample.mp4` di incognito. Hasilnya 403 karena video hanya bisa diakses lewat route ber-otorisasi.
2. Sebutkan bahwa 89 test Pest dan 139 pemeriksaan QA lulus. Detail ada di [TEST-PLAN](qa/TEST-PLAN.md).
3. Sebutkan bahwa kode dan dokumentasi bisa dicek di repo GitHub.

## 4. Poin yang bisa dijelaskan saat demo

- Video disimpan di disk private, bukan di `public`. Akses lewat route yang memeriksa login dan policy.
- Thumbnail disimpan di disk public karena bukan data sensitif.
- Reorder lesson memvalidasi bahwa daftar berisi semua lesson milik course, tanpa duplikat, lalu disimpan dalam transaksi.
- Upload video memakai XHR supaya progress bar bisa ditampilkan. Validasi tipe dan ukuran dilakukan di server.
- Role admin dijaga middleware, bukan hanya disembunyikan di tampilan.
- Kolom `role` tidak fillable, sehingga register publik tidak bisa membuat admin.
- Semua form memakai CSRF dan validasi FormRequest.
- Data demo memakai video contoh yang dibuat sendiri dan foto Unsplash dengan kredit.

## 5. Sebelum demo, cek hal ini

- [ ] MySQL berjalan dan database sudah dimigrasi.
- [ ] `php artisan storage:link` sudah dijalankan.
- [ ] `npm run build` sudah dijalankan, dan `public/hot` tidak ada.
- [ ] Data seed tersedia, minimal 3 course published.
- [ ] Admin bisa login.
- [ ] Video contoh bisa diputar.
- [ ] Browser tidak memakai cache lama, atau buka incognito.

## 6. Troubleshooting saat demo

| Masalah | Solusi |
|---|---|
| 419 Page Expired | Refresh halaman, pastikan `SESSION_DRIVER=database` dan tabel sessions ada |
| Thumbnail tidak tampil | Jalankan `php artisan storage:link` |
| Upload gagal, file terlalu besar | Kecilkan file atau naikkan batas PHP, lihat README |
| Video 404 | Pastikan status video sudah Siap |
| Video 403 | Course masih draft, ubah ke published |
| Halaman tanpa CSS | Jalankan `npm run build`, hapus `public/hot` |
| Data berantakan setelah demo | Jalankan `php artisan migrate:fresh --seed` |

## 7. Setelah demo

Kembalikan data ke kondisi awal:

```bash
php artisan migrate:fresh --seed
```

Perintah ini menghapus data baru, mengembalikan 4 course dan 12 lesson, serta membuat ulang file demo di storage.
