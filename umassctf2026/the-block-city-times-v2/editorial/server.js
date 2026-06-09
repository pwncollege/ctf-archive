const express   = require('express');
const puppeteer = require('puppeteer');
const path      = require('path');
const fs        = require('fs');

const PORT           = process.env.PORT           || 9000;
const APP_BASE_URL   = process.env.APP_BASE_URL   || 'http://localhost:8080';
const ADMIN_USERNAME = process.env.ADMIN_USERNAME || 'admin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'blockworld';

async function openInBrowser(fileUrl) {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-features=HttpsUpgrades',
    ],
  });

  try {
    const page = await browser.newPage();
    await page.goto(`${APP_BASE_URL}/login`, { waitUntil: 'networkidle0' });
    await page.type('#username', ADMIN_USERNAME);
    await page.type('#password', ADMIN_PASSWORD);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0' }),
      page.click('button[type="submit"]'),
    ]);

    const currentUrl = page.url();
    if (currentUrl.includes('/login')) {
      throw new Error('Login failed — still on login page after submit');
    }
    console.log("gothere")
    await page.goto(fileUrl, { waitUntil: 'networkidle0' });

    const text = await page.evaluate(() => document.body.innerHTML || '');

    return { text };
  } finally {
    await browser.close();
  }
}

const app = express();
app.use(express.json());

app.post('/submissions', async (req, res) => {
  const { title, author, description, filename } = req.body;

  if (!filename) {
    return res.status(400).json({ error: 'No filename provided.' });
  }

  res.json({ received: true, filename, title, author });

  const fileUrl = `${APP_BASE_URL}/files/${encodeURIComponent(filename)}`;

  try {
    await openInBrowser(fileUrl);
  } catch (err) {
    console.error('Puppeteer error:', err.message);
  }
});

app.listen(PORT, () => {
  console.log(`Editorial server running on http://localhost:${PORT}`);
});
