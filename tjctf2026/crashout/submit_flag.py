#!/usr/bin/env python3

import json
import sys
import subprocess
from datetime import datetime, timezone


URL = "https://ctf.tjctf.org/api/v1/challs/crashout/submit"
TOKEN = "EgleTcfxZcGQKaynxwT159mvsOb+Pxnu99q01EZdjVDr/P3AWtTZ+ZWhmiWw+JyZhdnzR/UnA8CywDivyZDVX/NbP7RNOBwrn9Es/+H5WjRiXSsnmZZHBsArV3km"


def main():
    if len(sys.argv) != 2:
        print("usage: submit_flag.py 'tjctf{...}'", file=sys.stderr)
        sys.exit(2)

    flag = sys.argv[1]
    try:
        proc = subprocess.run(
            [
                "curl",
                "-sS",
                URL,
                "-X",
                "POST",
                "-H",
                "Content-Type: application/json",
                "-H",
                f"Authorization: Bearer {TOKEN}",
                "-d",
                json.dumps({"flag": flag}),
            ],
            capture_output=True,
            text=True,
            check=False,
            timeout=20,
        )
        raw = (proc.stdout or proc.stderr).strip()
        status = proc.returncode
    except Exception as exc:
        raw = repr(exc)
        status = "ERROR"

    stamp = datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z")
    line = f"{stamp} | {flag} | {status} | {raw}\n"
    with open("tried_flags.txt", "a", encoding="utf-8") as f:
        f.write(line)

    print(status)
    print(raw)

    lowered = raw.lower()
    if "correct" in lowered or '"status":"correct"' in lowered or '"correct":true' in lowered:
        with open("FLAG.txt", "w", encoding="utf-8") as f:
            f.write(flag + "\n")


if __name__ == "__main__":
    main()
