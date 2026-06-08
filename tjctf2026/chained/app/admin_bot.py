#!/usr/bin/env python3
"""chained (TJCTF 2025) -- rehostable admin bot.

Python/Playwright port of the original admin-bot.js. Behaviour matches the
original exactly:

  original admin-bot.js:
      import flag from './flag.txt';
      urlRegex: /^https:\\/\\/chained\\.tjc\\.tf\\/admin\\//
      handler: page.goto(url + flag, ...); sleep(5000)

The bot:
  * reads the flag at runtime from FLAG_FILE (default /flag, falls back to
    ./flag.txt) -- the flag is NOT baked into any artifact;
  * only accepts URLs whose origin+path begins with <APP_ORIGIN>/admin/ (the
    local analog of the chained.tjc.tf/admin/ whitelist);
  * appends the flag to the submitted URL and visits it with a real headless
    Chromium, then waits, giving any same-origin script time to read
    location.href (which now contains the flag) and exfiltrate it.

This module exposes visit(submitted_url) for the solver to call in-process.
"""
import os
import time

FLAG_FILE = os.environ.get('FLAG_FILE', '/flag')


def read_flag():
    for candidate in (FLAG_FILE, os.path.join(os.path.dirname(__file__), 'flag.txt')):
        try:
            with open(candidate) as fh:
                return fh.read().strip()
        except OSError:
            continue
    raise RuntimeError('flag not found (set FLAG_FILE)')


def visit(submitted_url, app_origin, headless=True):
    """Mirror admin-bot.js: goto(submitted_url + flag)."""
    from playwright.sync_api import sync_playwright

    allowed_prefix = app_origin.rstrip('/') + '/admin/'
    if not submitted_url.startswith(allowed_prefix):
        raise ValueError(f'URL must start with {allowed_prefix!r}')

    flag = read_flag()
    target = submitted_url + flag

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=headless,
                                    args=['--no-sandbox', '--disable-setuid-sandbox'])
        page = browser.new_page()
        try:
            page.goto(target, timeout=5000, wait_until='domcontentloaded')
        except Exception:
            pass
        time.sleep(5)
        browser.close()
