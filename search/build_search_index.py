#!/usr/bin/env python3
"""Build the educator search index for CTF Archive.

Joins three sources that already live in the repo:
  * ``challenges.json``      - canonical per-event challenge index (type, path, raw_name)
  * ``search/tags.json``     - consolidated technique tags {tags, prerequisites, summary}
  * ``solves.csv``           - pwn.college solve records (difficulty signal)

and emits:
  * ``search/index.json``    - one record per challenge + facet summaries (the search data)
  * ``search/data.js``       - the same JSON wrapped as ``window.CTF_INDEX = {...}``
                               so the static page works when opened directly (file://).

Difficulty is computed ALGORITHMICALLY from two signals, blended:
  * solve count  (fewer pwn.college solves  -> harder)
  * CTF points   (more points, per-event percentile -> harder)
No LLM is involved at any point; the tags were produced offline and are treated
here as static data.

Run from the repo root:  python3 search/build_search_index.py
"""
from __future__ import annotations

import csv
import json
import re
from collections import Counter, defaultdict
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SEARCH = ROOT / "search"

CHALLENGES = ROOT / "challenges.json"
TAGS = SEARCH / "tags.json"
SOLVES = ROOT / "solves.csv"

OUT_JSON = SEARCH / "index.json"
OUT_JS = SEARCH / "data.js"

# id of the source dojo these challenges are imported from in generated dojo.yml
SOURCE_DOJO = "ctf-archive"

# Difficulty tiers (numeric -> label); higher number == harder.
TIER_LABELS = {1: "beginner", 2: "easy", 3: "medium", 4: "hard", 5: "insane"}

# Baseline tier per category, used only when a challenge has NEITHER solves nor points.
CATEGORY_BASELINE = {
    "PWN": 4, "REV": 4, "BLOCKCHAIN": 4,
    "CRYPTO": 3, "WEB": 3, "FORENSICS": 3, "HARDWARE": 3,
    "STEGO": 2, "OSINT": 2, "MISC": 2,
}

_POINTS_RE = re.compile(r"(\d+)\s*(pts?)?$", re.I)

# Leading category tokens to strip from a raw_name when deriving a clean display name.
CATEGORY_TOKENS = {
    "PWN", "BINARY", "EXPLOIT", "EXPLOITATION", "VULNERABILITY", "ROP",
    "REV", "REVERSE", "REVERSING", "CRYPTO", "CRYTPO", "HASHING", "RNG", "FRNG",
    "WEB", "WWW", "FORENSICS", "FORENSIC", "STEGO", "STEGANOGRAPHY", "OSINT",
    "RECON", "BLOCKCHAIN", "HARDWARE", "HARDWARE-RF", "MISC", "TRIVIA", "LOGICAL",
    "WARMUP", "SCRIPTING", "MALWARE", "PROG", "PROGRAMMING", "PPC", "MOBILE", "PWN/MISC",
}


def clean_name(raw_name: str, chal_id: str) -> str:
    """Derive a human display name: drop ONE leading category token, then leading
    points/number tokens. Falls back to a prettified id when nothing is left."""
    parts = [p.strip() for p in raw_name.split(" - ")]
    if parts and parts[0].upper() in CATEGORY_TOKENS:
        parts = parts[1:]
    while parts and re.fullmatch(r"\d+\s*(pts?)?", parts[0], re.I):
        parts = parts[1:]
    name = " - ".join(parts).strip()
    if not name:
        name = re.sub(r"[-_]+", " ", chal_id).strip().title() or chal_id
    return name


def extract_points(raw_name: str) -> int | None:
    """Pull the CTF point value out of a raw_name like 'PWN - 250 - Baby Heap'."""
    for tok in (t.strip() for t in raw_name.split(" - ")):
        m = re.fullmatch(r"(\d+)\s*(pts?)?", tok, re.I)
        if m:
            return int(m.group(1))
    return None


def solve_tier(n: int | None) -> int | None:
    if n is None:
        return None
    if n <= 2:
        return 5
    if n <= 5:
        return 4
    if n <= 15:
        return 3
    if n <= 50:
        return 2
    return 1


def points_tier(percentile: float | None) -> int | None:
    """percentile in [0,1], higher percentile == more points == harder."""
    if percentile is None:
        return None
    if percentile >= 0.90:
        return 5
    if percentile >= 0.70:
        return 4
    if percentile >= 0.40:
        return 3
    if percentile >= 0.15:
        return 2
    return 1


def blend(st: int | None, pt: int | None, category: str) -> tuple[int, str]:
    """Combine the solve tier and points tier into one (num, source)."""
    if st is not None and pt is not None:
        num = round(0.6 * st + 0.4 * pt)
        src = "solves+points"
    elif st is not None:
        num, src = st, "solves"
    elif pt is not None:
        num, src = pt, "points"
    else:
        num, src = CATEGORY_BASELINE.get(category, 3), "category"
    return max(1, min(5, num)), src


def load_solve_counts() -> Counter:
    counts: Counter = Counter()
    if not SOLVES.exists():
        return counts
    with SOLVES.open(newline="") as f:
        for row in csv.DictReader(f):
            counts[(row["module"], row["challenge"])] += 1
    return counts


def main() -> None:
    challenges = json.loads(CHALLENGES.read_text())
    tags = json.loads(TAGS.read_text()) if TAGS.exists() else {}
    solves = load_solve_counts()

    # --- per-event point values, for percentile ranking -------------------
    event_points: dict[str, list[int]] = defaultdict(list)
    for ev, evd in challenges.items():
        for c in evd["challenges"]:
            p = extract_points(c["raw_name"])
            if p is not None:
                event_points[ev].append(p)
    for ev in event_points:
        event_points[ev].sort()

    def percentile(ev: str, pts: int | None) -> float | None:
        if pts is None:
            return None
        arr = event_points.get(ev) or []
        if not arr:
            return None
        if len(arr) == 1:
            return 0.5
        # rank-based percentile: fraction of challenges at-or-below this points value
        below = sum(1 for x in arr if x < pts)
        equal = sum(1 for x in arr if x == pts)
        return (below + equal / 2) / len(arr)

    records = []
    tag_freq: Counter = Counter()
    prereq_freq: Counter = Counter()
    cat_freq: Counter = Counter()
    diff_freq: Counter = Counter()
    tagged = 0

    for ev, evd in challenges.items():
        event_name = evd.get("name", ev)
        for c in evd["challenges"]:
            path = c["path"]
            t = tags.get(path, {})
            ctags = list(t.get("tags", []))
            cprereq = list(t.get("prerequisites", []))
            summary = t.get("summary", "")
            if ctags or summary:
                tagged += 1

            n_solves = solves.get((ev, c["id"]))
            pts = extract_points(c["raw_name"])
            pct = percentile(ev, pts)
            num, src = blend(solve_tier(n_solves), points_tier(pct), c["type"])
            label = TIER_LABELS[num]

            rec = {
                "path": path,
                "module": ev,
                "id": c["id"],
                "name": clean_name(c["raw_name"], c["id"]),
                "event": event_name,
                "category": c["type"],
                "difficulty": label,
                "difficulty_num": num,
                "difficulty_source": src,
                "solve_count": n_solves,  # None if unknown
                "points": pts,            # None if unknown
                "tags": ctags,
                "prerequisites": cprereq,
                "summary": summary,
                "exists": c.get("exists", True),
            }
            records.append(rec)

            for tg in ctags:
                tag_freq[tg] += 1
            for pr in cprereq:
                prereq_freq[pr] += 1
            cat_freq[c["type"]] += 1
            diff_freq[label] += 1

    records.sort(key=lambda r: (r["module"], r["id"]))

    facets = {
        "categories": [
            {"key": k, "count": v} for k, v in sorted(cat_freq.items(), key=lambda kv: -kv[1])
        ],
        "difficulties": [
            {"key": TIER_LABELS[i], "count": diff_freq.get(TIER_LABELS[i], 0)}
            for i in range(1, 6)
        ],
        "tags": [
            {"tag": k, "count": v}
            for k, v in sorted(tag_freq.items(), key=lambda kv: (-kv[1], kv[0]))
        ],
        "prerequisites": [
            {"tag": k, "count": v}
            for k, v in sorted(prereq_freq.items(), key=lambda kv: (-kv[1], kv[0]))
        ],
    }

    index = {
        "generated_utc": datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
        "source_dojo": SOURCE_DOJO,
        "counts": {
            "challenges": len(records),
            "events": len(challenges),
            "tagged": tagged,
            "distinct_tags": len(tag_freq),
        },
        "tier_labels": TIER_LABELS,
        "facets": facets,
        "challenges": records,
    }

    OUT_JSON.write_text(json.dumps(index, ensure_ascii=False, indent=2) + "\n")
    OUT_JS.write_text(
        "// Auto-generated by build_search_index.py - do not edit by hand.\n"
        "window.CTF_INDEX = "
        + json.dumps(index, ensure_ascii=False, separators=(",", ":"))
        + ";\n"
    )

    print(f"wrote {OUT_JSON.relative_to(ROOT)} and {OUT_JS.relative_to(ROOT)}")
    print(
        f"  {len(records)} challenges, {tagged} tagged, "
        f"{len(tag_freq)} distinct tags, {len(challenges)} events"
    )
    print("  difficulty distribution:",
          {TIER_LABELS[i]: diff_freq.get(TIER_LABELS[i], 0) for i in range(1, 6)})


if __name__ == "__main__":
    main()
