# E2E QA scripts

Dua skrip Node.js + [Playwright](https://playwright.dev) untuk menguji aplikasi dari sisi HTTP dan browser sungguhan. Script ini **tidak** bagian dari build aplikasi dan tidak menambah dependency di `package.json`.

| Script | Cakupan | Jumlah skenario |
|---|---|---|
| `qa-http.mjs` | Status code, redirect, session/cookie, CSRF, otorisasi, validasi, upload, streaming + Range, cek file di disk | 35 |
| `qa-ui.mjs` | Form login/CRUD, upload + progress bar, drag & drop reorder, modal hapus, playback video, responsive mobile, screenshot | 15 |

Laporan lengkap + hasil eksekusi: `docs/qa/TEST-PLAN.md`.

## Prasyarat

- Aplikasi berjalan (default `http://127.0.0.1:8000`) dengan data demo (`php artisan migrate:fresh --seed`) dan `php artisan storage:link`.
- Node.js 20+.
- Playwright + Chromium:

```bash
npm install --no-save playwright
npx playwright install chromium
```

## Menjalankan

```bash
node tests/e2e/qa-http.mjs
node tests/e2e/qa-ui.mjs
```

Variabel environment:

| Variabel | Default | Keterangan |
|---|---|---|
| `BASE_URL` | `http://127.0.0.1:8000` | Alamat aplikasi |
| `APP_ROOT` | folder kerja (`process.cwd()`) | Dipakai untuk fixture gambar/video dan folder screenshot |

Keduanya menulis hasil ke `results-http.json` / `results-ui.json` di folder kerja. Screenshot QA UI disimpan ke `docs/qa/screenshots/`.

## Peringatan

Skrip ini **membuat, mengubah, dan menghapus data** serta mengunggah file video/thumbnail. Jalankan pada database khusus pengujian (mis. `afindo_qa`), jangan pada database demo yang ingin dipertahankan.

Contoh isolasi:

```env
DB_CONNECTION=mysql
DB_DATABASE=afindo_qa
```

lalu `php artisan migrate:fresh --seed` untuk database tersebut sebelum menjalankan QA.
