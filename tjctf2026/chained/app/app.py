#!/usr/bin/env python3
"""chained (TJCTF 2025) -- rehostable Flask app.

Faithful port of the original web/chained/app.py. The only change versus the
original is operational, not behavioural:

  * host/port are taken from CHAINED_HOST / CHAINED_PORT (default 0.0.0.0:5000).

The admin-bot submission ("The admin will visit your URL") is served by the
companion bot service (bot_server.py): the player submits a URL to the bot, the
bot enforces the original <origin>/admin/ whitelist, reads the flag at runtime
from /flag, and loads <url>+<flag> -- exactly as the original Node admin-bot.js
imported flag from ./flag.txt and did page.goto(url + flag).

The flag itself is NOT in this file (or any served artifact).
"""
import os

from flask import Flask, request, render_template, redirect, url_for
import requests

app = Flask(__name__)


def isSafe(url):
    blacklist = {'127', 'local', '2130706433', '017700000001', '::1', '0.0.0.0',
                 '[::]', 'ffff', '0.0.0.0', '0x', '..', '%2e%2e', '@'}
    return all([i not in url.lower() for i in blacklist])


@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        url = request.form['url'] or ''
        if not isSafe(url):
            return 'Access denied. URL parameter included one or more of the blacklisted keywords.'
        return redirect(url_for('index', url=url))
    url = request.args.get('url') or ''
    if url:
        desc = 'The admin will visit your URL.'
        try:
            req = 'Your response: ' + requests.get(url).text
        except Exception:
            return 'Uh-oh... Try again!'
    else:
        req, desc = '', ''
    return render_template('index.html', q=req, desc=desc)


@app.route('/admin')
def js():
    if request.remote_addr != '127.0.0.1':
        return 'Access denied. Page only accessible from server side.'
    query = request.args.get("q", "")
    return query, 200, {'Content-Type': 'application/javascript'}


if __name__ == '__main__':
    host = os.environ.get('CHAINED_HOST', '0.0.0.0')
    port = int(os.environ.get('CHAINED_PORT', '5000'))
    app.run(host=host, port=port, threaded=True)
