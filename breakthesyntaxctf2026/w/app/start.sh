#!/bin/sh
# Launch the app server (:8000) and the admin bot (:3000) in one container.
# server.js POSTs accepted publishes to http://localhost:3000/visit, so both
# run side by side. The admin password is shared between them and randomised per
# boot so it cannot be guessed. The flag is read by the bot from /flag at
# runtime (FLAG_FILE); it is never present in any served artifact.
set -e

export ADMIN_PASSWORD="${ADMIN_PASSWORD:-$(head -c 18 /dev/urandom | od -An -tx1 | tr -d ' \n')}"
export APP_ORIGIN="${APP_ORIGIN:-http://localhost:8000}"
export FLAG_FILE="${FLAG_FILE:-/flag}"

node bot.js &
BOT_PID=$!

# If the bot dies (e.g. missing flag), take the whole container down.
trap 'kill $BOT_PID 2>/dev/null' EXIT

node server.js
