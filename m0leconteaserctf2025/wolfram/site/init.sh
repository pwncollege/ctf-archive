#!/bin/bash
set -e
here="$(realpath "$(dirname "$0")")"
cd "$here"

echo "---------- generate https certificate ----------"
openssl req -x509 -newkey RSA:4096 -keyform PEM -keyout "./key.pem" -outform PEM -out "./cert.pem" -days 3650 --nodes -subj "/CN="
