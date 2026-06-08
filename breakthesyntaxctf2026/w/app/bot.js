const express = require('express');
const puppeteer = require('puppeteer-core');
const fs = require('fs');

const APP_ORIGIN = process.env.APP_ORIGIN || 'http://localhost:8000';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD;
if (!ADMIN_PASSWORD) { console.error('ADMIN_PASSWORD not set'); process.exit(1); }

// The flag is read at runtime from FLAG_FILE (default /flag); it is never baked
// into any served artifact. The admin bot types it into the Notes textarea.
const FLAG_FILE = process.env.FLAG_FILE || '/flag';
let FLAG;
try { FLAG = fs.readFileSync(FLAG_FILE, 'utf8').trim(); }
catch (e) { FLAG = process.env.FLAG; }
if (!FLAG) { console.error(`flag not found (FLAG_FILE=${FLAG_FILE} and FLAG unset)`); process.exit(1); }

const app = express();
app.use(express.json());
let visitCounter = 0;

function log(reqId, msg, details) {
  const suffix = details ? ` ${JSON.stringify(details)}` : '';
  console.log(`[bot ${new Date().toISOString()} #${reqId}] ${msg}${suffix}`);
}

function errorDetails(e) {
  return {
    name: e && e.name,
    message: e && e.message,
    stack: e && e.stack,
  };
}

async function step(reqId, name, fn) {
  const started = Date.now();
  log(reqId, `START ${name}`);
  try {
    const result = await fn();
    log(reqId, `DONE ${name}`, { ms: Date.now() - started });
    return result;
  } catch (e) {
    log(reqId, `FAIL ${name}`, { ms: Date.now() - started, error: errorDetails(e) });
    throw e;
  }
}

function attachPageLogging(reqId, page) {
  page.on('request', (request) => {
    log(reqId, 'PAGE request', {
      method: request.method(),
      resourceType: request.resourceType(),
      url: request.url(),
    });
  });
  page.on('requestfinished', (request) => {
    log(reqId, 'PAGE request finished', {
      method: request.method(),
      resourceType: request.resourceType(),
      url: request.url(),
    });
  });
  page.on('console', (msg) => {
    log(reqId, 'PAGE console', {
      type: msg.type(),
      text: msg.text(),
      location: msg.location(),
    });
  });
  page.on('pageerror', (err) => {
    log(reqId, 'PAGE error', errorDetails(err));
  });
  page.on('requestfailed', (request) => {
    log(reqId, 'PAGE request failed', {
      method: request.method(),
      url: request.url(),
      failure: request.failure(),
    });
  });
  page.on('response', (response) => {
    const status = response.status();
    if (status >= 400) {
      log(reqId, 'PAGE bad response', {
        status,
        url: response.url(),
      });
    }
  });
  page.on('framenavigated', (frame) => {
    if (frame === page.mainFrame()) {
      log(reqId, 'PAGE navigated', { url: frame.url() });
    }
  });
}

async function logPageState(reqId, page) {
  try {
    const state = await page.evaluate(() => {
      const viewState = {};
      for (const id of ['auth-view', 'feed-view', 'viewer-view', 'profile-view', 'bio-editor']) {
        const el = document.getElementById(id);
        viewState[id] = el ? {
          exists: true,
          hidden: el.classList.contains('hidden'),
          childCount: el.childElementCount,
        } : { exists: false };
      }

      return {
        href: location.href,
        readyState: document.readyState,
        title: document.title,
        navigateType: typeof window.navigate,
        hasToken: Boolean(localStorage.getItem('token')),
        views: viewState,
        feedItems: document.querySelectorAll('#feed-list .post').length,
        wasmErrorVisible: !document.getElementById('wasm-error')?.classList.contains('hidden'),
      };
    });
    log(reqId, 'PAGE state snapshot', state);
  } catch (e) {
    log(reqId, 'PAGE state snapshot failed', errorDetails(e));
  }
}

app.post('/visit', async (req, res) => {
  const reqId = ++visitCounter;
  const { url } = req.body;
  log(reqId, 'VISIT received', { url, appOrigin: APP_ORIGIN });
  if (!url || !url.startsWith(APP_ORIGIN)) {
    log(reqId, 'VISIT rejected', { reason: 'Invalid URL' });
    return res.status(400).json({ error: 'Invalid URL' });
  }

  let browser;
  let page;
  try {
    browser = await step(reqId, 'launch chromium', () => puppeteer.launch({
      executablePath: '/usr/bin/chromium',
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
      headless: true,
    }));

    page = await step(reqId, 'open new page', () => browser.newPage());
    attachPageLogging(reqId, page);
    page.setDefaultTimeout(30000);
    page.setDefaultNavigationTimeout(30000);

    await step(reqId, 'goto app origin', () => page.goto(APP_ORIGIN, { waitUntil: 'domcontentloaded' }));
    await step(reqId, 'admin login fetch', () => page.evaluate(async (creds) => {
      const r = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(creds),
      });
      const data = await r.json();
      if (data.token) localStorage.setItem('token', data.token);
      return { ok: data.ok, username: data.username, hasToken: Boolean(data.token), error: data.error };
    }, { username: 'admin', password: ADMIN_PASSWORD }).then((result) => {
      log(reqId, 'admin login result', result);
    }));

    await step(reqId, 'goto submitted url', () => page.goto(url, { waitUntil: 'networkidle2', timeout: 10000 }));
    await step(reqId, 'settle after submitted url', () => new Promise(r => setTimeout(r, 2000)));

    await step(reqId, 'navigate to feed', () => page.evaluate(() => {
      if (typeof window.navigate !== 'function') {
        throw new Error('window.navigate is not defined');
      }
      window.navigate('');
    }));
    await step(reqId, 'wait for feed view', () => page.waitForFunction(
      () => !document.getElementById('feed-view').classList.contains('hidden'),
      { timeout: 5000 },
    ));

    const editBtn = await step(reqId, 'wait for Edit button', () => page.waitForSelector('aria/Edit[role="button"]'));
    await step(reqId, 'click Edit button', () => editBtn.click());
    const textarea = await step(reqId, 'wait for notes textarea', () => page.waitForSelector('aria/What\'s on your mind?[role="textbox"]'));
    await step(reqId, 'fill notes textarea', () => textarea.evaluate((el, flag) => { el.value = `Dear diary, this is the worst post I've ever seen. This incident will be reported. ${flag}`; }, FLAG));
    const saveBtn = await step(reqId, 'wait for Save button', () => page.waitForSelector('aria/Save[role="button"]'));
    await step(reqId, 'click Save button', () => saveBtn.click());

    await step(reqId, 'settle after save', () => new Promise(r => setTimeout(r, 2000)));

    await step(reqId, 'close browser', () => browser.close());
    browser = null;

    log(reqId, 'VISIT complete');
    res.json({ ok: true });
  } catch (e) {
    log(reqId, 'VISIT failed', errorDetails(e));
    if (page) {
      await logPageState(reqId, page);
    }
    if (browser) {
      await step(reqId, 'close browser after failure', () => browser.close()).catch(() => { });
    }
    res.status(500).json({ error: e.message });
  }
});

app.listen(3000, () => console.log(`Bot :3000 APP_ORIGIN=${APP_ORIGIN}`));
