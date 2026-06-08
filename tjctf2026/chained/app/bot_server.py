#!/usr/bin/env python3
"""chained (TJCTF 2025) -- admin-bot HTTP service.

Wraps admin_bot.visit() behind a tiny HTTP endpoint so the Flask app can ask
the bot to visit a submitted URL, exactly like the original admin-bot platform
("The admin will visit your URL").

  POST /visit  {"url": "<submitted>"}

The bot:
  * only accepts URLs whose origin+path begins with <APP_ORIGIN>/admin/ (the
    local analog of the original chained.tjc.tf/admin/ whitelist);
  * reads the flag at runtime from /flag (see admin_bot.read_flag);
  * appends the flag to the submitted URL and loads it with a real headless
    Chromium (admin_bot.visit), giving any same-origin script time to read
    location.href -- which now contains the flag.

The flag is NEVER present in any served artifact; it lives only in /flag and is
appended in-browser by the bot.
"""
import os

from flask import Flask, request, jsonify

import admin_bot

APP_ORIGIN = os.environ.get('APP_ORIGIN', 'http://app:5000')

app = Flask(__name__)


@app.route('/visit', methods=['POST'])
def visit():
    data = request.get_json(silent=True) or {}
    url = data.get('url') or ''
    try:
        admin_bot.visit(url, app_origin=APP_ORIGIN, headless=True)
        return jsonify(ok=True)
    except ValueError as e:
        return jsonify(ok=False, error=str(e)), 400
    except Exception as e:
        return jsonify(ok=False, error=str(e)), 500


@app.route('/healthz')
def healthz():
    return 'ok'


if __name__ == '__main__':
    port = int(os.environ.get('BOT_PORT', '8080'))
    app.run(host='0.0.0.0', port=port, threaded=True)
