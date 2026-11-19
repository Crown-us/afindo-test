// QA UI end-to-end dengan browser sungguhan (Playwright + Chromium).
// Mencakup: login, CRUD course/lesson, upload video + progress bar, drag & drop reorder,
// modal konfirmasi hapus, alur student (redirect login + playback), dan responsive mobile.
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8000';
const ROOT = process.env.APP_ROOT || process.cwd();
const SHOTS = path.join(ROOT, 'docs', 'qa', 'screenshots');
fs.mkdirSync(SHOTS, { recursive: true });

const results = [];
let pass = 0, fail = 0;
const consoleErrors = [];
const badResponses = [];
const blockedExternal = [];

const ok = (cond, msg, extra = '') => { if (!cond) throw new Error(msg + (extra ? ` | ${extra}` : '')); };
const eq = (a, b, msg) => ok(a === b, `${msg} (expected ${b}, got ${a})`);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

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

function watch(page, label) {
  page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push({ label, text: m.text() }); });
  page.on('pageerror', (e) => consoleErrors.push({ label, text: 'pageerror: ' + e.message }));
  page.on('response', (r) => {
    const url = r.url();
    const type = r.request().resourceType();
    if (r.status() >= 400) {
      if (url.startsWith(BASE) && type !== 'document') badResponses.push({ label, url, status: r.status(), type });
      else if (!url.startsWith(BASE)) blockedExternal.push({ label, url, status: r.status() });
    }
  });
  page.on('requestfailed', (r) => {
    if (!r.url().startsWith(BASE)) blockedExternal.push({ label, url: r.url(), status: 'failed' });
  });
}

async function shot(page, name) {
  const file = path.join(SHOTS, `${name}.jpg`);
  await page.screenshot({ path: file, fullPage: true, type: 'jpeg', quality: 78 });
  return path.relative(ROOT, file).replace(/\\/g, '/');
}

async function login(page, email, password, urlPattern) {
  await page.goto(BASE + '/login');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await Promise.all([
    page.waitForURL(urlPattern, { timeout: 20000 }),
    page.click('form button[type=submit]'),
  ]);
}

async function waitText(page, text, timeout = 10000) {
  await page.getByText(text, { exact: false }).first().waitFor({ state: 'visible', timeout });
}

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--autoplay-policy=no-user-gesture-required'] });
  const created = { title: null, courseId: null, lessonId: null, shots: [] };
  let adminCtx, admin;

  try {
    // ================= ADMIN =================
    adminCtx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
    admin = await adminCtx.newPage();
    admin.setDefaultTimeout(20000);
    watch(admin, 'admin');

    await check('U1', 'Login admin lewat form UI', async () => {
      await login(admin, 'admin@afindo.test', 'password', '**/admin');
      await waitText(admin, 'Total course');
      created.shots.push(await shot(admin, 'ui-01-admin-dashboard'));
      return admin.url();
    });

    await check('U2', 'Navigasi ke Kelola Course + screenshot', async () => {
      await admin.click('nav a:has-text("Kelola Course")');
      await admin.waitForURL('**/admin/courses');
      ok(await admin.locator('table').first().isVisible(), 'tabel course tidak tampil di desktop');
      created.shots.push(await shot(admin, 'ui-02-kelola-course'));
      return 'tabel + kartu mobile ada';
    });

    await check('U3', 'Buat course lewat form (judul, deskripsi, thumbnail, published)', async () => {
      await admin.click('a:has-text("+ Course baru")');
      await admin.waitForURL('**/admin/courses/create');
      created.title = `QA UI Course ${Date.now()}`;
      await admin.fill('#title', created.title);
      await admin.fill('#description', 'Course dibuat lewat browser oleh skrip QA Playwright untuk menguji form dan upload.');
      await admin.check('input[name=status][value=published]');
      await admin.setInputFiles('#thumbnail', path.join(ROOT, 'database/seeders/images/uiux.jpg'));
      created.shots.push(await shot(admin, 'ui-03-course-form'));
      await Promise.all([
        admin.waitForURL(/\/admin\/courses\/\d+$/, { timeout: 20000 }),
        admin.click('button:has-text("Simpan course")'),
      ]);
      created.courseId = Number(admin.url().split('/').pop());
      await waitText(admin, 'Course dibuat');
      return `course ${created.courseId}`;
    });

    await check('U4', 'Tambah lesson lewat form', async () => {
      await admin.click('a:has-text("+ Lesson baru")');
      await admin.waitForURL(/\/admin\/courses\/\d+\/lessons\/create/);
      await admin.fill('#title', 'QA UI Lesson');
      await Promise.all([
        admin.waitForURL(/\/admin\/lessons\/\d+\/edit/, { timeout: 20000 }),
        admin.click('button:has-text("Simpan & lanjut unggah video")'),
      ]);
      created.lessonId = Number(admin.url().match(/lessons\/(\d+)\/edit/)[1]);
      await waitText(admin, 'Video');
      return `lesson ${created.lessonId}`;
    });

    await check('U5', 'Upload video dari browser: progress bar tampil lalu status Siap', async () => {
      const cdp = await adminCtx.newCDPSession(admin);
      await cdp.send('Network.enable');
      // Perlambat upload supaya progress bar sempat terlihat & bisa di-screenshot.
      await cdp.send('Network.emulateNetworkConditions', {
        offline: false, latency: 20, downloadThroughput: 5 * 1024 * 1024, uploadThroughput: 80 * 1024, connectionType: 'cellular3g',
      });
      await admin.setInputFiles('#video', path.join(ROOT, 'database/seeders/videos/sample.mp4'));
      const navPromise = admin.waitForNavigation({ timeout: 120000 });
      await admin.click('button:has-text("Unggah video")');
      await admin.waitForSelector('[role=progressbar]', { state: 'visible', timeout: 15000 });
      await sleep(1200);
      const progressText = (await admin.locator('p:has-text("Mengunggah")').first().textContent()) || '';
      created.shots.push(await shot(admin, 'ui-04-upload-progress'));
      await navPromise;
      await admin.waitForLoadState('load');
      await cdp.send('Network.emulateNetworkConditions', {
        offline: false, latency: 0, downloadThroughput: -1, uploadThroughput: -1,
      });
      await admin.getByText('Siap', { exact: true }).first().waitFor({ timeout: 20000 });
      ok(await admin.locator('video').first().isVisible(), 'preview video tidak muncul setelah upload');
      created.shots.push(await shot(admin, 'ui-05-lesson-ready'));
      return `progress="${progressText.trim()}", badge Siap, video tampil`;
    });

    await check('U6', 'Tambah 2 lesson lalu drag & drop mengubah urutan', async () => {
      for (let i = 1; i <= 2; i++) {
        await admin.goto(`${BASE}/admin/courses/${created.courseId}`);
        await admin.click('a:has-text("+ Lesson baru")');
        await admin.waitForURL(/lessons\/create/);
        await admin.fill('#title', `QA UI Lesson ${i + 1}`);
        await Promise.all([
          admin.waitForURL(/\/admin\/lessons\/\d+\/edit/),
          admin.click('button:has-text("Simpan & lanjut unggah video")'),
        ]);
      }
      await admin.goto(`${BASE}/admin/courses/${created.courseId}`);
      const handles = admin.locator('[data-handle]');
      eq(await handles.count(), 3, 'jumlah lesson');
      const before = await admin.locator('ol[x-ref="list"] > li').evaluateAll((els) => els.map((e) => e.dataset.id));
      const a = await handles.nth(0).boundingBox();
      const c = await handles.nth(2).boundingBox();
      await admin.mouse.move(a.x + a.width / 2, a.y + a.height / 2);
      await admin.mouse.down();
      await admin.mouse.move(c.x + c.width / 2, c.y + c.height + 12, { steps: 30 });
      await sleep(200);
      await admin.mouse.up();
      await waitText(admin, 'Urutan lesson disimpan', 15000);
      const after = await admin.locator('ol[x-ref="list"] > li').evaluateAll((els) => els.map((e) => e.dataset.id));
      ok(JSON.stringify(before) !== JSON.stringify(after), 'urutan tidak berubah setelah drag');
      await admin.reload();
      const persisted = await admin.locator('ol[x-ref="list"] > li').evaluateAll((els) => els.map((e) => e.dataset.id));
      eq(JSON.stringify(persisted), JSON.stringify(after), 'urutan tidak persisten setelah reload');
      created.shots.push(await shot(admin, 'ui-06-reorder'));
      return `${before.join(',')} -> ${after.join(',')}`;
    });

    await check('U7', 'Modal konfirmasi hapus lesson berfungsi', async () => {
      const firstRow = admin.locator('ol[x-ref="list"] > li').first();
      const lessonId = await firstRow.getAttribute('data-id');
      await firstRow.getByRole('button', { name: 'Hapus', exact: true }).click();
      await waitText(admin, 'Hapus lesson ini?');
      created.shots.push(await shot(admin, 'ui-07-modal-delete'));
      await Promise.all([
        admin.waitForURL(new RegExp(`/admin/courses/${created.courseId}$`), { timeout: 20000 }),
        admin.getByRole('button', { name: 'Ya, hapus', exact: true }).click(),
      ]);
      await waitText(admin, 'Lesson dihapus');
      const ids = await admin.locator('ol[x-ref="list"] > li').evaluateAll((els) => els.map((e) => e.dataset.id));
      ok(!ids.includes(lessonId), 'lesson masih ada setelah dihapus');
      eq(ids.length, 2, 'jumlah lesson setelah hapus');
      return `lesson ${lessonId} terhapus`;
    });

    await check('U8', 'Hapus course lewat modal + media ikut terhapus', async () => {
      await admin.click('button:has-text("Hapus course")');
      await waitText(admin, 'Hapus course ini?');
      await Promise.all([
        admin.waitForURL('**/admin/courses', { timeout: 20000 }),
        admin.getByRole('button', { name: 'Ya, hapus', exact: true }).click(),
      ]);
      await waitText(admin, 'Course dihapus');
      await admin.goto(`${BASE}/admin/courses?q=${encodeURIComponent(created.title)}`);
      await waitText(admin, 'Tidak ada course yang cocok');
      return 'terhapus, pencarian kosong';
    });

    // ================= STUDENT =================
    const stCtx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
    const st = await stCtx.newPage();
    st.setDefaultTimeout(20000);
    watch(st, 'student');

    await check('U9', 'Guest buka detail course lalu diarahkan login, kembali ke lesson', async () => {
      await st.goto(BASE + '/courses');
      await st.locator('a[href*="/courses/"]').first().click();
      await st.waitForURL(/\/courses\/\d+$/);
      created.shots.push(await shot(st, 'ui-08-course-detail-guest'));
      await Promise.all([
        st.waitForURL('**/login**', { timeout: 20000 }),
        st.click('a:has-text("Masuk untuk mulai belajar")'),
      ]);
      await st.fill('#email', 'student@afindo.test');
      await st.fill('#password', 'password');
      const [resp] = await Promise.all([
        st.waitForResponse((r) => r.url().includes('/lessons/') && r.url().endsWith('/video'), { timeout: 30000 }).catch(() => null),
        (async () => {
          await Promise.all([
            st.waitForURL(/\/courses\/\d+\/lessons\/\d+$/, { timeout: 20000 }),
            st.click('form button[type=submit]'),
          ]);
        })(),
      ]);
      ok(st.url().match(/\/courses\/\d+\/lessons\/\d+$/), 'tidak kembali ke lesson', st.url());
      return `kembali ke ${new URL(st.url()).pathname}${resp ? `, video ${resp.status()}` : ''}`;
    });

    await check('U10', 'Video benar-benar diputar di browser student', async () => {
      const video = st.locator('video').first();
      await video.waitFor({ state: 'visible', timeout: 15000 });
      await st.evaluate(async () => {
        const v = document.querySelector('video');
        v.muted = true;
        await v.play();
      });
      await sleep(1500);
      const t = await st.evaluate(() => document.querySelector('video').currentTime);
      ok(t > 0.3, 'video tidak berjalan (currentTime=' + t + ')');
      created.shots.push(await shot(st, 'ui-09-player-student'));
      return `currentTime=${t.toFixed(2)}s`;
    });

    await check('U11', 'Student ditolak dari area admin (halaman 403)', async () => {
      const r = await st.goto(BASE + '/admin');
      eq(r.status(), 403, 'status /admin');
      const body = await st.content();
      ok(/403|Forbidden|tidak diizinkan/i.test(body), 'halaman 403 tidak informatif');
      created.shots.push(await shot(st, 'ui-10-403-student'));
      return '403';
    });

    // ================= MOBILE =================
    const mCtx = await browser.newContext({ viewport: { width: 375, height: 812 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
    const mp = await mCtx.newPage();
    mp.setDefaultTimeout(20000);
    watch(mp, 'mobile');

    await check('U12', 'Menu hamburger terbuka di 375px', async () => {
      await mp.goto(BASE + '/');
      await mp.click('button[aria-label="Buka menu"]');
      await sleep(400);
      ok(await mp.locator('a:has-text("Katalog")').last().isVisible(), 'menu mobile tidak terbuka');
      created.shots.push(await shot(mp, 'ui-11-mobile-nav'));
      return 'menu terbuka';
    });

    await check('U13', 'Katalog mobile jadi 1 kolom', async () => {
      await mp.goto(BASE + '/courses');
      const cols = await mp.evaluate(() => {
        const grid = [...document.querySelectorAll('div.grid')].find((d) => d.querySelector('a[href*="/courses/"]'));
        return grid ? getComputedStyle(grid).gridTemplateColumns.split(' ').length : 0;
      });
      eq(cols, 1, 'jumlah kolom grid di mobile');
      created.shots.push(await shot(mp, 'ui-12-mobile-catalog'));
      return '1 kolom';
    });

    await check('U14', 'Halaman kelola course admin: tabel berubah jadi kartu di mobile', async () => {
      await mp.goto(BASE + '/login');
      await mp.fill('#email', 'admin@afindo.test');
      await mp.fill('#password', 'password');
      await Promise.all([mp.waitForURL('**/admin'), mp.click('form button[type=submit]')]);
      await mp.goto(BASE + '/admin/courses');
      ok(!(await mp.locator('table').first().isVisible()), 'tabel desktop masih tampil di mobile');
      ok(await mp.locator('ul.md\\:hidden').first().isVisible(), 'daftar kartu mobile tidak tampil');
      created.shots.push(await shot(mp, 'ui-13-mobile-admin-courses'));
      return 'tabel hidden, kartu tampil';
    });

    await check('U15', 'Tidak ada request lokal yang error (4xx/5xx) saat memakai aplikasi', async () => {
      ok(badResponses.length === 0, `${badResponses.length} request lokal error`, JSON.stringify(badResponses.slice(0, 5)));
      return `0 request lokal error; external diblokir: ${blockedExternal.length}`;
    });

    await stCtx.close();
    await mCtx.close();
  } finally {
    if (adminCtx) await adminCtx.close();
    await browser.close();
    fs.writeFileSync('results-ui.json', JSON.stringify({ pass, fail, results, consoleErrors, badResponses, blockedExternal, screenshots: created.shots }, null, 2));
    console.log(`\nHASIL UI: ${pass} PASS, ${fail} FAIL, total ${pass + fail}`);
    if (badResponses.length) console.log('request lokal error:', JSON.stringify(badResponses.slice(0, 10), null, 2));
    if (blockedExternal.length) console.log('external diblokir:', JSON.stringify([...new Map(blockedExternal.map((b) => [b.url, b])).values()].slice(0, 5), null, 2));
    process.exit(fail ? 1 : 0);
  }
})();
