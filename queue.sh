#!/usr/bin/env bash
# Browse the ctf-archive challenge index (challenges.json).
#
# usage:
#   ./queue.sh                              show this help
#   ./queue.sh sample [TYPE] [N]            print N random challenges of TYPE
#   ./queue.sh list   [TYPE] [CTF]          list challenges, optionally filtered
#   ./queue.sh ctfs                         list all CTFs with challenge counts
#   ./queue.sh types                        list canonical types with counts
#   ./queue.sh rebuild                      regenerate challenges.json
#
# Back-compat: ./queue.sh <type> [n]  →  same as `sample <type> <n>`.
# TYPE is one of: PWN REV CRYPTO WEB FORENSICS STEGO OSINT HARDWARE BLOCKCHAIN MISC

set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INDEX="$ROOT/challenges.json"

ensure_index() {
  if [[ ! -f "$INDEX" ]]; then
    echo "challenges.json missing — running build_index.py" >&2
    python3 "$ROOT/build_index.py"
  fi
}

upper() { tr '[:lower:]' '[:upper:]' <<<"$1"; }

cmd_help() { sed -n '2,/^$/p' "$0" | sed 's/^# \?//'; }

cmd_rebuild() { python3 "$ROOT/build_index.py"; }

cmd_types() {
  ensure_index
  python3 - "$INDEX" <<'PY'
import json, sys, collections
d = json.load(open(sys.argv[1]))
c = collections.Counter(ch["type"] for v in d.values() for ch in v["challenges"])
for t, n in c.most_common():
    print(f"{t:12s} {n}")
PY
}

cmd_ctfs() {
  ensure_index
  python3 - "$INDEX" <<'PY'
import json, sys
d = json.load(open(sys.argv[1]))
for k, v in sorted(d.items()):
    print(f"{k:35s} {len(v['challenges']):4d}  {v['name']}")
PY
}

cmd_list() {
  ensure_index
  local type="${1:-}" ctf="${2:-}"
  [[ -n "$type" ]] && type="$(upper "$type")"
  python3 - "$INDEX" "$type" "$ctf" <<'PY'
import json, sys
idx, want_type, want_ctf = json.load(open(sys.argv[1])), sys.argv[2], sys.argv[3]
for ctf, v in sorted(idx.items()):
    if want_ctf and want_ctf.lower() not in ctf.lower():
        continue
    rows = [c for c in v["challenges"] if not want_type or c["type"] == want_type]
    if not rows:
        continue
    print(f"\n# {ctf} — {v['name']}")
    for c in rows:
        marker = "" if c["exists"] else "  (missing dir)"
        print(f"  [{c['type']:9s}] {c['path']:55s}  {c['name']}{marker}")
PY
}

cmd_sample() {
  ensure_index
  local type="${1:-PWN}" n="${2:-5}"
  type="$(upper "$type")"
  python3 - "$INDEX" "$type" "$n" "$ROOT" <<'PY'
import json, sys, random, pathlib
idx, want_type, n, root = json.load(open(sys.argv[1])), sys.argv[2], int(sys.argv[3]), pathlib.Path(sys.argv[4])
pool = [(ctf, c) for ctf, v in idx.items() for c in v["challenges"]
        if c["type"] == want_type and c["exists"]]
if not pool:
    sys.exit(f"no challenges found for type {want_type}")
for ctf, c in random.sample(pool, min(n, len(pool))):
    path = root / c["path"]
    print(f"== {c['path']} [{c['type']}] — {c['name']} ==")
    desc = path / "DESCRIPTION.md"
    if desc.is_file():
        head = "".join(desc.read_text(errors="replace").splitlines(keepends=True)[:3])
        print(head.rstrip())
    print()
PY
}

main() {
  if [[ $# -eq 0 ]]; then cmd_help; exit 0; fi
  local cmd="$1"; shift || true
  case "$cmd" in
    -h|--help|help)   cmd_help ;;
    list)             cmd_list "$@" ;;
    ctfs)             cmd_ctfs ;;
    types)            cmd_types ;;
    sample)           cmd_sample "$@" ;;
    rebuild)          cmd_rebuild ;;
    *)
      # back-compat: first arg looks like a type → run sample
      cmd_sample "$cmd" "$@"
      ;;
  esac
}

main "$@"
