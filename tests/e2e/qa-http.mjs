// QA HTTP-level untuk Afindo Learn (Laravel) — dijalankan terhadap server lokal MySQL.
// Fokus: auth, role, CRUD, upload video, reorder, validasi, dan celah keamanan umum.
import { request } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8000';
const ROOT = process.env.APP_ROOT || process.cwd();
const STORAGE = path.join(ROOT, 'storage', 'app');

const results = [];
let pass = 0, fail = 0;
let publishedIds = [];
let draftId = null;
let draftLessonIds = [];
let publishedLessonIds = [];
let publishedCourseId = null;

const ok = (cond, msg, extra = '') => { if (!cond) throw new Error(msg + (extra ? ` | ${extra}` : '')); };
const eq = (a, b, msg) => ok(a === b, `${msg} (expected ${b}, got ${a})`);

async function check(id, name, fn) {
  const t0 = Date.now();
  try {
    const detail = await fn();
    pass++; results.push({ id, name, status: 'PASS', ms: Date.now() - t0, detail: detail ?? '' });
    console.log(`PASS ${id}  ${name}${detail ? `  [${detail}]` : ''}`);
  } catch (e) {
    fail++; results.push({ id, name, status: 'FAIL', ms: Date.now() - t0, detail: e.message });
    console.log(`FAIL ${id}  ${name}  -> ${e.message}`);
  }
}

function makeCtx() {
  return request.newContext({ baseURL: BASE, maxRedirects: 0, failOnStatusCode: false });
}

async function token(ctx, page = '/login') {
  const r = await ctx.get(page);
  const body = await r.text();
  const m = body.match(/name="csrf-token" content="([^"]+)"/);
  ok(m, `meta csrf tidak ditemukan di ${page}`);
  return m[1];
}

async function login(ctx, email, password) {
  const t = await token(ctx);
  return ctx.post('/login', { form: { _token: t, email, password }, maxRedirects: 0 });
}

const idsOf = (body, re) => [...new Set([...body.matchAll(re)].map((m) => Number(m[1])))];

async function main() {
  const guest = await makeCtx();
  const admin = await makeCtx();
  const student = await makeCtx();

  // ---------- A. Publik / guest ----------
  await check('A1', 'Beranda tampil untuk guest', async () => {
    const r = await guest.get('/');
    eq(r.status(), 200, 'status /');
    const b = await r.text();
    ok(b.includes('Belajar skill digital'), 'hero tidak tampil');
    ok(b.includes('Course terbaru'), 'section course terbaru tidak ada');
    ok(b.includes('Daftar gratis'), 'tombol daftar tidak ada');
    return '200, hero + course terbaru';
  });

  await check('A2', 'Katalog hanya menampilkan course published', async () => {
    const r = await guest.get('/courses');
    eq(r.status(), 200, 'status /courses');
    const b = await r.text();
    publishedIds = idsOf(b, /href="[^"]*\/courses\/(\d+)"/g);
    ok(publishedIds.length >= 3, `course published ${publishedIds.length}`);
    ok(b.includes('Dasar Pemrograman Web'), 'course Laravel tidak tampil');
    ok(!b.includes('Membangun REST API'), 'course draft ikut tampil di katalog');
    return `${publishedIds.length} course`;
  });

  await check('A3', 'Pencarian katalog bekerja', async () => {
    const r = await guest.get('/courses?q=Git');
    const b = await r.text();
    ok(b.includes('Git dan GitHub'), 'hasil Git tidak muncul');
    ok(!b.includes('Dasar Pemrograman Web'), 'hasil tidak terfilter');
    return '1 hasil relevan';
  });

  await check('A4', 'Pencarian aman dari injeksi SQL', async () => {
    const r = await guest.get('/courses?q=' + encodeURIComponent("' OR 1=1 --"));
    eq(r.status(), 200, 'status');
    const b = await r.text();
    ok(!/SQLSTATE|QueryException|Stack trace/i.test(b), 'ada error SQL di response');
    return '200 tanpa error';
  });

  await check('A5', 'Halaman login & daftar tampil', async () => {
    const l = await guest.get('/login');
    const d = await guest.get('/register');
    eq(l.status(), 200, 'login'); eq(d.status(), 200, 'register');
    ok((await l.text()).includes('Masuk ke akunmu'), 'form login tidak ada');
    return 'login + register 200';
  });

  await check('A6', 'Guest tidak bisa membuka lesson (redirect login)', async () => {
    const r = await guest.get(`/courses/${publishedCourseId}/lessons/${publishedLessonIds[0]}`);
    eq(r.status(), 302, 'status');
    ok(r.headers()['location'].includes('/login'), 'tidak redirect ke login');
    return '302 -> /login';
  });

  await check('A7', 'Guest tidak bisa stream video', async () => {
    const r = await guest.get(`/lessons/${publishedLessonIds[0]}/video`);
    eq(r.status(), 302, 'status');
    ok(r.headers()['location'].includes('/login'), 'tidak redirect ke login');
    return '302 -> /login';
  });

  // ---------- B. Auth ----------
  await check('B1', 'Login admin berhasil dan masuk area admin', async () => {
    const r = await login(admin, 'admin@afindo.test', 'password');
    eq(r.status(), 302, 'status login');
    eq(r.headers()['location'], BASE + '/dashboard', 'redirect setelah login');
    const d = await admin.get('/admin');
    eq(d.status(), 200, 'status /admin');
    ok((await d.text()).includes('Halo, Admin Afindo'), 'dashboard admin tidak tampil');
    return 'admin -> /admin 200';
  });

  await check('B2', 'Login student berhasil dan ditolak dari /admin', async () => {
    const r = await login(student, 'student@afindo.test', 'password');
    eq(r.status(), 302, 'status login');
    const a = await student.get('/admin');
    eq(a.status(), 403, 'student /admin');
    const d = await student.get('/dashboard');
    eq(d.status(), 302, 'dashboard student');
    ok(d.headers()['location'].includes('/courses'), 'student tidak diarahkan ke katalog');
    return 'login OK, /admin 403';
  });

  await check('B3', 'Password salah ditolak tanpa login', async () => {
    const ctx = await makeCtx();
    const r = await login(ctx, 'admin@afindo.test', 'salah-banget');
    eq(r.status(), 302, 'status');
    ok(!r.headers()['location'].includes('/admin'), 'kok bisa masuk admin');
    const page = await ctx.get('/login');
    const b = await page.text();
    ok(/text-red-600|credentials|kredensial|salah/i.test(b), 'pesan error tidak tampil');
    return 'ditolak + pesan error';
  });

  await check('B4', 'Request tanpa CSRF ditolak 419', async () => {
    const ctx = await makeCtx();
    const r = await ctx.post('/login', { form: { email: 'admin@afindo.test', password: 'password' } });
    eq(r.status(), 419, 'status');
    return '419';
  });

  await check('B5', 'Register tidak bisa menaikkan role jadi admin', async () => {
    const ctx = await makeCtx();
    const t = await token(ctx, '/register');
    const email = `qa-${Date.now()}@afindo.test`;
    const r = await ctx.post('/register', {
      form: {
        _token: t, name: 'QA Student', email, password: 'password', password_confirmation: 'password', role: 'admin',
      },
      maxRedirects: 0,
    });
    eq(r.status(), 302, 'status register');
    const a = await ctx.get('/admin');
    eq(a.status(), 403, 'user baru seharusnya student, bukan admin');
    return 'role injection diabaikan (403)';
  });

  // ---------- C. Dashboard & daftar admin ----------
  await check('C1', 'Dashboard admin menampilkan statistik benar', async () => {
    const r = await admin.get('/admin');
    const b = await r.text();
    ok(b.includes('Total course') && b.includes('Total lesson') && b.includes('Video siap putar'), 'label statistik tidak lengkap');
    ok(b.includes('>4<'), 'total course bukan 4 di dashboard');
    ok(b.includes('>12<'), 'total lesson bukan 12');
    ok(b.includes('10 / 12'), 'video siap putar bukan 10 / 12');
    ok(b.includes('Membangun REST API'), 'heatmap lesson butuh video tidak tampil');
    return '4 course, 12 lesson, 10/12 video';
  });

  await check('C2', 'Kelola course: filter status & pencarian', async () => {
    const all = await (await admin.get('/admin/courses')).text();
    draftId = (all.match(/<tr[\s\S]*?<\/tr>/g) || [])
      .filter((row) => row.includes('Draft'))
      .map((row) => (row.match(/\/admin\/courses\/(\d+)/) || [])[1])
      .filter(Boolean)
      .map(Number)[0] ?? null;
    ok(draftId, 'course draft tidak ditemukan di kelola course');
    const onlyDraft = await (await admin.get('/admin/courses?status=draft')).text();
    ok(onlyDraft.includes('Membangun REST API'), 'filter draft tidak menampilkan draft');
    ok(!onlyDraft.includes('Dasar Pemrograman Web'), 'filter draft masih menampilkan published');
    const search = await (await admin.get('/admin/courses?q=Git')).text();
    ok(search.includes('Git dan GitHub'), 'pencarian admin tidak bekerja');
    return `draft course id=${draftId}`;
  });

  await check('C3', 'Validasi form course (kosong) ditolak', async () => {
    const t = await token(admin, '/admin/courses/create');
    const r = await admin.post('/admin/courses', { form: { _token: t, title: '', description: '', status: 'draft' } });
    eq(r.status(), 302, 'status (redirect balik)');
    const b = await (await admin.get('/admin/courses/create')).text();
    ok(/wajib|required/i.test(b), 'pesan validasi tidak tampil');
    return '302 + pesan validasi';
  });

  // ---------- D. CRUD course + lesson + video ----------
  let courseId = null, courseTitle = null, thumbnailUrl = null;
  let lessonId = null, lessonTitle = null;

  await check('D1', 'Admin membuat course + thumbnail', async () => {
    courseTitle = `QA Course ${Date.now()}`;
    const t = await token(admin, '/admin/courses/create');
    const r = await admin.post('/admin/courses', {
      multipart: {
        _token: t,
        title: courseTitle,
        description: 'Course yang dibuat otomatis oleh skrip QA end-to-end untuk pengujian.',
        status: 'draft',
        thumbnail: { name: 'thumb.jpg', mimeType: 'image/jpeg', buffer: fs.readFileSync(path.join(ROOT, 'database/seeders/images/git.jpg')) },
      },
    });
    eq(r.status(), 302, 'status');
    const loc = r.headers()['location'];
    courseId = Number((loc.match(/\/admin\/courses\/(\d+)/) || [])[1]);
    ok(courseId, 'id course tidak ditemukan di redirect', loc);
    const show = await admin.get(`/admin/courses/${courseId}`);
    eq(show.status(), 200, 'detail course');
    const b = await show.text();
    ok(b.includes(courseTitle), 'judul tidak tampil');
    const tm = b.match(/src="([^"]*\/storage\/thumbnails\/[^"]+)"/);
    ok(tm, 'thumbnail tidak dirender');
    thumbnailUrl = tm[1].replace('http://localhost:8000', BASE);
    const tr = await guest.get(thumbnailUrl);
    eq(tr.status(), 200, 'thumbnail harus bisa diakses');
    ok((tr.headers()['content-type'] || '').includes('image'), 'content-type thumbnail');
    return `course ${courseId}, thumbnail ${tr.status()}`;
  });

  await check('D2', 'Course draft tidak muncul di katalog publik', async () => {
    const b = await (await guest.get('/courses')).text();
    ok(!b.includes(courseTitle), 'course draft bocor ke katalog');
    return 'tersembunyi';
  });

  await check('D3', 'Admin menambah lesson (posisi terakhir)', async () => {
    lessonTitle = `QA Lesson ${Date.now()}`;
    const t = await token(admin, `/admin/courses/${courseId}/lessons/create`);
    const r = await admin.post(`/admin/courses/${courseId}/lessons`, {
      form: { _token: t, title: lessonTitle, description: 'Lesson untuk pengujian upload dan reorder.' },
    });
    eq(r.status(), 302, 'status');
    const loc = r.headers()['location'];
    ok(/\/admin\/lessons\/\d+\/edit/.test(loc), 'tidak diarahkan ke edit lesson', loc);
    lessonId = Number((loc.match(/\/admin\/lessons\/(\d+)\/edit/) || [])[1]);
    const edit = await admin.get(loc);
    ok((await edit.text()).includes(lessonTitle), 'judul lesson tidak tampil');
    return `lesson ${lessonId}`;
  });

  await check('D4', 'Upload video lewat endpoint XHR (JSON)', async () => {
    const t = await token(admin, `/admin/lessons/${lessonId}/edit`);
    const r = await admin.post(`/admin/lessons/${lessonId}/video`, {
      multipart: { video: { name: 'sample.mp4', mimeType: 'video/mp4', buffer: fs.readFileSync(path.join(ROOT, 'database/seeders/videos/sample.mp4')) } },
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': t },
    });
    eq(r.status(), 200, 'status upload');
    const j = await r.json();
    ok(/berhasil/i.test(j.message || ''), 'pesan sukses upload', JSON.stringify(j));
    const dir = path.join(STORAGE, 'private', 'videos', `course-${courseId}`, `lesson-${lessonId}`);
    const files = fs.existsSync(dir) ? fs.readdirSync(dir).filter((f) => f.endsWith('.mp4')) : [];
    ok(files.length === 1, 'file video tidak tersimpan di disk private', dir);
    const edit = await (await admin.get(`/admin/lessons/${lessonId}/edit`)).text();
    ok(edit.includes('Siap'), 'badge status bukan Siap');
    ok(edit.includes('<video'), 'preview video tidak tampil');
    return `200, file ${files[0]}`;
  });

  await check('D5', 'Upload file non-video ditolak 422', async () => {
    const t = await token(admin, `/admin/lessons/${lessonId}/edit`);
    const r = await admin.post(`/admin/lessons/${lessonId}/video`, {
      multipart: { video: { name: 'bukan-video.txt', mimeType: 'text/plain', buffer: Buffer.from('ini bukan video') } },
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': t },
    });
    eq(r.status(), 422, 'status');
    return '422';
  });

  await check('D6', 'Streaming video + HTTP Range berjalan', async () => {
    const r = await student.get(`/lessons/${publishedLessonIds[0] ?? 1}/video`);
    eq(r.status(), 200, 'status stream');
    ok((r.headers()['content-type'] || '').includes('video/mp4'), 'content-type');
    const rr = await student.get(`/lessons/${publishedLessonIds[0] ?? 1}/video`, { headers: { Range: 'bytes=0-99' } });
    eq(rr.status(), 206, 'status range');
    ok((rr.headers()['content-range'] || '').startsWith('bytes 0-99/'), 'content-range salah');
    return '200 + 206 partial';
  });

  await check('D7', 'Video course draft ditolak untuk student (403)', async () => {
    const r = await student.get(`/lessons/${draftLessonIds[0]}/video`);
    eq(r.status(), 403, 'status');
    return '403';
  });

  await check('D8', 'Admin bisa preview video course draft, student ditolak', async () => {
    // Buat draft course + lesson + video sendiri, supaya tidak bergantung data seed.
    const t0 = await token(admin, '/admin/courses/create');
    const c = await admin.post('/admin/courses', {
      form: { _token: t0, title: `QA Draft Preview ${Date.now()}`, description: 'Draft untuk menguji preview video oleh admin.', status: 'draft' },
    });
    const cid = Number((c.headers()['location'].match(/\/admin\/courses\/(\d+)/) || [])[1]);
    const t1 = await token(admin, `/admin/courses/${cid}/lessons/create`);
    const l = await admin.post(`/admin/courses/${cid}/lessons`, { form: { _token: t1, title: 'QA Draft Video' } });
    const lid = Number((l.headers()['location'].match(/\/admin\/lessons\/(\d+)\/edit/) || [])[1]);
    const t2 = await token(admin, `/admin/lessons/${lid}/edit`);
    const up = await admin.post(`/admin/lessons/${lid}/video`, {
      multipart: { video: { name: 'sample.mp4', mimeType: 'video/mp4', buffer: fs.readFileSync(path.join(ROOT, 'database/seeders/videos/sample.mp4')) } },
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': t2 },
    });
    eq(up.status(), 200, 'upload video draft');
    const a = await admin.get(`/lessons/${lid}/video`);
    eq(a.status(), 200, 'admin preview draft');
    const s = await student.get(`/lessons/${lid}/video`);
    eq(s.status(), 403, 'student draft');
    const t3 = await token(admin, `/admin/courses/${cid}`);
    await admin.post(`/admin/courses/${cid}`, { form: { _token: t3, _method: 'DELETE' } });
    return 'admin 200, student 403';
  });

  await check('D9', 'Reorder lesson tersimpan dan tervalidasi', async () => {
    // buat 2 lesson tambahan supaya reorder bermakna
    for (let i = 0; i < 2; i++) {
      const t = await token(admin, `/admin/courses/${courseId}/lessons/create`);
      await admin.post(`/admin/courses/${courseId}/lessons`, { form: { _token: t, title: `QA Reorder ${i} ${Date.now()}` } });
    }
    const show = await (await admin.get(`/admin/courses/${courseId}`)).text();
    const lessonIds = idsOf(show, /data-id="(\d+)"/g);
    eq(lessonIds.length, 3, 'jumlah lesson');
    const t = await token(admin, `/admin/courses/${courseId}`);
    const reversed = [...lessonIds].reverse();
    const r = await admin.patch(`/admin/courses/${courseId}/lessons/reorder`, {
      data: { lessons: reversed },
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': t },
    });
    eq(r.status(), 200, 'status reorder');
    const after = await (await admin.get(`/admin/courses/${courseId}`)).text();
    const orderAfter = idsOf(after, /data-id="(\d+)"/g);
    eq(JSON.stringify(orderAfter), JSON.stringify(reversed), 'urutan tidak tersimpan');
    // payload tidak lengkap harus 422
    const bad = await admin.patch(`/admin/courses/${courseId}/lessons/reorder`, {
      data: { lessons: [reversed[0]] },
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': t },
    });
    eq(bad.status(), 422, 'reorder tidak lengkap seharusnya 422');
    return `${lessonIds.join(',')} -> ${reversed.join(',')}`;
  });

  await check('D10', 'Publish course muncul di katalog publik', async () => {
    const t = await token(admin, `/admin/courses/${courseId}/edit`);
    const r = await admin.post(`/admin/courses/${courseId}`, {
      form: { _token: t, _method: 'PUT', title: courseTitle, description: 'Course yang dibuat otomatis oleh skrip QA end-to-end untuk pengujian.', status: 'published' },
    });
    eq(r.status(), 302, 'status update');
    const b = await (await guest.get(`/courses?q=${encodeURIComponent('QA Course')}`)).text();
    ok(b.includes(courseTitle), 'course published tidak muncul di katalog');
    return 'published terlihat';
  });

  await check('D11', 'Hapus lesson menghapus file video & merapatkan urutan', async () => {
    const edit = await (await admin.get(`/admin/lessons/${lessonId}/edit`)).text();
    ok(edit.includes('Hapus') || true, 'noop');
    const t = await token(admin, `/admin/courses/${courseId}`);
    const r = await admin.post(`/admin/lessons/${lessonId}`, { form: { _token: t, _method: 'DELETE' } });
    eq(r.status(), 302, 'status delete');
    const dir = path.join(STORAGE, 'private', 'videos', `course-${courseId}`, `lesson-${lessonId}`);
    ok(!fs.existsSync(dir) || fs.readdirSync(dir).length === 0, 'file video lesson masih ada');
    const show = await (await admin.get(`/admin/courses/${courseId}`)).text();
    ok(!show.includes(lessonTitle), 'lesson masih tampil');
    const positions = [...show.matchAll(/data-position[^>]*>(\d+)</g)].map((m) => Number(m[1]));
    eq(JSON.stringify(positions), JSON.stringify(positions.map((_, i) => i + 1)), 'posisi tidak rapat');
    return 'terhapus, posisi rapat';
  });

  await check('D12', 'Hapus course menghapus media terkait', async () => {
    const t = await token(admin, `/admin/courses/${courseId}`);
    const r = await admin.post(`/admin/courses/${courseId}`, { form: { _token: t, _method: 'DELETE' } });
    eq(r.status(), 302, 'status delete');
    const show = await admin.get(`/admin/courses/${courseId}`);
    eq(show.status(), 404, 'course masih ada');
    const dir = path.join(STORAGE, 'private', 'videos', `course-${courseId}`);
    ok(!fs.existsSync(dir), 'folder video course masih ada');
    const thumbPath = path.join(STORAGE, 'public', 'thumbnails', path.basename(new URL(thumbnailUrl).pathname));
    ok(!fs.existsSync(thumbPath), 'file thumbnail masih ada');
    return 'course + media terhapus';
  });

  // ---------- E. Otorisasi ----------
  await check('E1', 'Student tidak bisa aksi manajemen course', async () => {
    const t = await token(student, '/courses');
    const r = await student.post('/admin/courses', { form: { _token: t, title: 'Nope QA', description: 'harusnya ditolak', status: 'draft' } });
    eq(r.status(), 403, 'status');
    return '403';
  });

  await check('E2', 'Student hanya lihat course published', async () => {
    const pub = await student.get(`/courses/${publishedCourseId}`);
    eq(pub.status(), 200, 'course published');
    const draft = await student.get(`/courses/${draftId}`);
    eq(draft.status(), 404, 'course draft');
    return 'published 200, draft 404';
  });

  await check('E3', 'Lesson dari course lain di URL = 404', async () => {
    const r = await student.get(`/courses/${publishedCourseId}/lessons/${draftLessonIds[0]}`);
    eq(r.status(), 404, 'status');
    return '404';
  });

  // ---------- F. Keamanan ----------
  await check('F1', 'Video private tidak bisa diakses lewat URL /storage', async () => {
    const r = await guest.get('/storage/videos/course-1/lesson-1/sample.mp4');
    eq(r.status(), 403, 'status');
    return '403';
  });

  await check('F2', 'Path traversal ke .env ditolak', async () => {
    const r = await guest.get('/storage/..%2F..%2F.env');
    ok([403, 404].includes(r.status()), 'status tidak aman', String(r.status()));
    return String(r.status());
  });

  await check('F3', 'Judul course berisi script di-escape (XSS)', async () => {
    const t = await token(admin, '/admin/courses/create');
    const r = await admin.post('/admin/courses', {
      form: { _token: t, title: '<script>alert(1)</script> XSS QA', description: 'Course uji XSS oleh skrip QA otomatis.', status: 'draft' },
    });
    eq(r.status(), 302, 'status create');
    const id = Number((r.headers()['location'].match(/\/admin\/courses\/(\d+)/) || [])[1]);
    const body = await (await admin.get(`/admin/courses/${id}`)).text();
    ok(body.includes('&lt;script&gt;alert(1)&lt;/script&gt;'), 'script tidak di-escape');
    ok(!body.includes('<script>alert(1)</script>'), 'script mentah ikut terender');
    const t2 = await token(admin, `/admin/courses/${id}`);
    await admin.post(`/admin/courses/${id}`, { form: { _token: t2, _method: 'DELETE' } });
    return 'escaped, course uji dihapus';
  });

  // ---------- G. Alur intended redirect ----------
  await check('G1', 'Guest buka lesson -> login -> kembali ke lesson', async () => {
    const ctx = await makeCtx();
    const target = `/courses/${publishedCourseId}/lessons/${publishedLessonIds[0]}`;
    const r = await ctx.get(target);
    eq(r.status(), 302, 'status awal');
    const loginPage = await ctx.get('/login');
    const b = await loginPage.text();
    const t = (b.match(/name="_token" value="([^"]+)"/) || [])[1];
    ok(t, 'token login tidak ada');
    const post = await ctx.post('/login', { form: { _token: t, email: 'siti@afindo.test', password: 'password' } });
    eq(post.status(), 302, 'status login');
    eq(post.headers()['location'], BASE + target, 'tidak kembali ke lesson');
    return 'kembali ke ' + target;
  });

  // discover published lesson ids for later use (done after E; harmless)
  await check('G2', 'Halaman player student: navigasi prev/next', async () => {
    const b = await (await student.get(`/courses/${publishedCourseId}/lessons/${publishedLessonIds[0]}`)).text();
    ok(b.includes('Materi course'), 'daftar materi tidak ada');
    ok(b.includes('Lesson berikutnya'), 'tombol next tidak ada');
    return 'player + daftar materi';
  });

  await guest.dispose(); await admin.dispose(); await student.dispose();
  fs.writeFileSync(path.join(process.cwd(), 'results-http.json'), JSON.stringify({ pass, fail, results }, null, 2));
  console.log(`\nHASIL: ${pass} PASS, ${fail} FAIL, total ${pass + fail}`);
  process.exit(fail ? 1 : 0);
}

// Data seed: cari id course & lesson yang dipakai beberapa test.
async function discover() {
  const admin = await makeCtx();
  await login(admin, 'admin@afindo.test', 'password');
  const courses = await (await admin.get('/admin/courses')).text();
  const rows = [...courses.matchAll(/<tr[\s\S]*?<\/tr>/g)].map((m) => m[0]);
  const publishedRow = rows.find((r) => r.includes('Published'));
  const draftRow = rows.find((r) => r.includes('Draft'));
  publishedCourseId = Number((publishedRow?.match(/\/admin\/courses\/(\d+)/) || [])[1]);
  draftId = Number((draftRow?.match(/\/admin\/courses\/(\d+)/) || [])[1]);
  if (!publishedCourseId || !draftId) throw new Error('gagal menemukan course published/draft di halaman admin');
  publishedLessonIds = idsOf(await (await admin.get(`/admin/courses/${publishedCourseId}`)).text(), /\/admin\/lessons\/(\d+)\/edit/g);
  draftLessonIds = idsOf(await (await admin.get(`/admin/courses/${draftId}`)).text(), /\/admin\/lessons\/(\d+)\/edit/g);
  await admin.dispose();
}

await discover();
await main();
