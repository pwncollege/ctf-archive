#!/bin/sh

echo "[+] Waiting for connections"
socaz --timeout 300 --bind 1337 --cmd "python3 /src/entrypoint.py"
echo "[+] Exiting"