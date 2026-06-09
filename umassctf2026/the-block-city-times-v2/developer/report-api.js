const puppeteer = require('puppeteer');
const fs        = require('fs');
const path      = require('path');

const BASE_URL        = process.env.BASE_URL        || 'http://localhost:8080';
const ADMIN_USERNAME  = process.env.ADMIN_USERNAME  || 'admin';
const ADMIN_PASSWORD  = process.env.ADMIN_PASSWORD  || 'changed_on_remote';
const REPORT_ENDPOINT = process.env.REPORT_ENDPOINT || '/api/config';
const DEFAULT_FLAG    = 'UMASS{{rehost_test_2e259a16}}';
const FLAG_PATH       = '/flag';

function readFlag() {
  try {
    return fs.readFileSync(FLAG_PATH, 'utf8').trim() || DEFAULT_FLAG;
  } catch (_) {
    return DEFAULT_FLAG;
  }
}

const FLAG = readFlag();

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  try {
    const page = await browser.newPage();

    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
    await page.type('#username', ADMIN_USERNAME);
    await page.type('#password', ADMIN_PASSWORD);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0' }),
      page.click('button[type="submit"]'),
    ]);

    if (page.url().includes('/login')) {
      console.error('Login failed.');
      process.exitCode = 1;
      return;
    }

    await page.setCookie({
      name: 'FLAG',
      value: FLAG,
    });

    const targetUrl = `${BASE_URL}${REPORT_ENDPOINT}`;
    const response  = await page.goto(targetUrl, { waitUntil: 'networkidle0' });
    const content   = await page.evaluate(() => document.body?.innerText || '');

    const status = response.status();
    console.log(JSON.stringify({
      endpoint:   targetUrl,
      timestamp:  new Date().toISOString(),
      httpStatus: status,
      isError:    status >= 400,
      content:    content.slice(0, 4000),
    }, null, 2));

  } finally {
    await browser.close();
  }
})();
