const { chromium } = require(process.env.UI_PLAYWRIGHT_PATH);
const assert = require('node:assert/strict');
const fs = require('node:fs');

(async () => {
    const browser = await chromium.launch({ executablePath: process.env.UI_BROWSER_PATH, headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8015';
    const folder = 'storage/app/ui-audit';
    fs.mkdirSync(folder, { recursive: true });
    async function check(name) {
        await page.waitForTimeout(200);
        assert.equal(await page.getByText('Internal Server Error', { exact: true }).count(), 0, name + ': server error');
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth);
        assert.equal(overflow, false, name + ': page overflows');
        await page.screenshot({ path: folder + '/' + name + '.png', fullPage: true });
        console.log('PASS ' + name);
    }
    try {
        await page.goto(base + '/login');
        await page.waitForFunction(() => !!window.Alpine);
        await check('login-desktop');
        await page.getByLabel('Kata sandi', { exact: false }).first().fill('preview');
        await page.getByRole('button', { name: 'Tampilkan kata sandi' }).click();
        assert.equal(await page.locator('#password').getAttribute('type'), 'text');
        await page.setViewportSize({ width: 390, height: 740 });
        await check('login-mobile');
        await page.goto(base + '/login/scan');
        await check('scan-mobile');
        await page.route('**/login/scan', async route => {
            if (route.request().method() === 'POST') {
                await new Promise(resolve => setTimeout(resolve, 500));
                return route.fulfill({ status: 403, contentType: 'application/json', body: JSON.stringify({ success: false, message: 'Akun tidak aktif.' }) });
            }
            return route.continue();
        });
        await page.locator('#barcode_input').fill('INVALID-PREVIEW');
        await page.getByRole('button', { name: 'Masuk dengan NIK' }).click();
        await page.getByRole('alert').filter({ hasText: 'Akun tidak aktif.' }).waitFor();
        assert.equal(await page.getByRole('button', { name: 'Masuk dengan NIK' }).isEnabled(), true);
        await page.unroute('**/login/scan');
        await page.goto(base + '/login');
        await page.locator('#user_id').fill(process.env.UI_ADMIN_ID || 'ADMIN001');
        await page.locator('#password').fill(process.env.UI_ADMIN_PASSWORD);
        await Promise.all([page.waitForURL('**/admin/dashboard'), page.getByRole('button', { name: 'Masuk ke Pojok Ilmu' }).click()]);
        await check('matrix-mobile');
        await page.getByRole('button', { name: 'Buka menu', exact: true }).click();
        await page.locator('#app-sidebar').getByRole('link', { name: 'Kompetensi & target' }).click();
        await page.waitForURL('**/admin/skills');
        await check('skills-mobile');
        await page.getByRole('button', { name: /Tambah Skill Baru/ }).click();
        await page.getByRole('dialog', { name: 'Tambah Master Skill Baru' }).waitFor();
        await check('skill-modal-mobile');
        await page.keyboard.press('Escape');
        await page.getByRole('dialog', { name: 'Tambah Master Skill Baru' }).waitFor({ state: 'hidden' });
        await page.getByRole('button', { name: /Atur Target Level/ }).click();
        await check('target-modal-mobile');
        await page.getByRole('button', { name: 'Batal', exact: true }).click();
        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.goto(base + '/admin/trainings');
        await check('trainings-desktop');
        await page.getByRole('button', { name: 'Tambah pelatihan', exact: true }).click();
        await check('training-modal-desktop');
        await page.keyboard.press('Escape');
        const detailLink = page.getByRole('link', { name: /Kelola materi/ }).first();
        if (await detailLink.count()) {
            await detailLink.click();
            await check('training-detail-desktop');
            await page.getByRole('button', { name: 'Tambah Materi Pelatihan', exact: true }).click();
            await check('material-modal-desktop');
            await page.keyboard.press('Escape');
            const addQuestion = page.getByRole('button', { name: 'Tambah Soal Kuis', exact: true });
            if (await addQuestion.count()) {
                await addQuestion.click();
                await check('question-modal-desktop');
                await page.keyboard.press('Escape');
            }
            await page.getByRole('button', { name: 'Hapus', exact: true }).first().click();
            await page.getByRole('button', { name: 'Batal', exact: true }).click();
            await page.setViewportSize({ width: 390, height: 740 });
            await check('training-detail-mobile');
        }
        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.goto(base + '/admin/dashboard');
        await check('matrix-desktop');
        await page.getByRole('button', { name: 'Ganti tema terang atau gelap' }).click();
        await check('matrix-dark');
        await page.getByRole('button', { name: 'Keluar', exact: true }).click();
        await page.waitForURL('**/login');
        await page.locator('#user_id').fill(process.env.UI_EMPLOYEE_ID);
        await Promise.all([page.waitForURL('**/operator/dashboard'), page.getByRole('button', { name: 'Masuk ke Pojok Ilmu' }).click()]);
        await page.getByRole('button', { name: 'Ganti tema terang atau gelap' }).click();
        await check('operator-desktop');
        await page.setViewportSize({ width: 390, height: 740 });
        await check('operator-mobile');
        for (const name of ['material', 'assessment', 'result']) {
            const file = folder + '/' + name + '.html';
            if (!fs.existsSync(file)) continue;
            await page.route('**/__ui_preview__/' + name, route => route.fulfill({
                status: 200, contentType: 'text/html', body: fs.readFileSync(file, 'utf8').replaceAll('http://pojok-ilmu.test', base),
            }));
            await page.goto(base + '/__ui_preview__/' + name);
            await check(name + '-mobile');
            if (name === 'assessment') {
                await page.getByRole('radio').first().check();
                await page.getByRole('button', { name: 'Kirim jawaban', exact: true }).click();
                await page.getByRole('button', { name: 'Periksa kembali', exact: true }).click();
                assert.equal(await page.getByRole('radio').first().isChecked(), true);
            }
            await page.setViewportSize({ width: 1440, height: 1000 });
            await check(name + '-desktop');
            await page.setViewportSize({ width: 390, height: 740 });
        }
        assert.deepEqual(errors, [], 'Browser errors');
        console.log('UI AUDIT PASSED');
    } finally {
        if (errors.length) console.log('BROWSER ERRORS', JSON.stringify(errors));
        await browser.close();
    }
})().catch(e => { console.error(e); process.exitCode = 1; });
