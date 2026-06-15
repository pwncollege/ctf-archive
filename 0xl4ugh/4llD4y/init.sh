#!/bin/sh

set -eu

cd /challenge
cat /flag > /flag_$(head -c 8 /dev/urandom | od -An -tx1 | tr -d ' ').txt
npm install --silent
exec node /challenge/app.js
