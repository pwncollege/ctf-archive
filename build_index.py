#!/usr/bin/env python3
"""Scan every CTF/module.yml and emit challenges.json keyed by CTF id.

Each challenge gets a normalized type from this canonical set:
PWN, REV, CRYPTO, WEB, FORENSICS, STEGO, OSINT, BLOCKCHAIN, HARDWARE,
BLOCKCHAIN, MISC, UNKNOWN.
"""
import json
import re
import sys
from pathlib import Path

try:
    import yaml
except ImportError:
    sys.exit("PyYAML required: pip install pyyaml")

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "challenges.json"

# Map raw prefix tokens (uppercased, stripped) to canonical type.
TYPE_MAP = {
    "PWN": "PWN",
    "BINARY": "PWN",
    "BINARY EXPLOITATION": "PWN",
    "EXPLOIT": "PWN",
    "EXPLOITATION": "PWN",
    "VULNERABILITY": "PWN",
    "ROP": "PWN",
    "PWN/MISC": "PWN",
    "REV": "REV",
    "REVERSE": "REV",
    "REVERSING": "REV",
    "CRYPTO": "CRYPTO",
    "CRYTPO": "CRYPTO",
    "HASHING": "CRYPTO",
    "RNG": "CRYPTO",
    "FRNG": "CRYPTO",
    "WEB": "WEB",
    "WWW": "WEB",
    "FORENSICS": "FORENSICS",
    "FORENSIC": "FORENSICS",
    "STEGO": "STEGO",
    "STEGANOGRAPHY": "STEGO",
    "OSINT": "OSINT",
    "RECON": "OSINT",
    "SOCIAL ENGINEERING": "OSINT",
    "BLOCKCHAIN": "BLOCKCHAIN",
    "HARDWARE-RF": "HARDWARE",
    "HARDWARE": "HARDWARE",
    "RADIO FREQUENCY": "HARDWARE",
    "MISC": "MISC",
    "TRIVIA": "MISC",
    "LOGICAL": "MISC",
    "WARMUP": "MISC",
}

# Per-challenge id keyword fallback when prefix is missing/non-standard.
KEYWORD_FALLBACK = [
    (re.compile(r"rsa|aes|cipher|crypt|hash|lfsr|prng", re.I), "CRYPTO"),
    (re.compile(r"rev|reverse|crackme", re.I), "REV"),
    (re.compile(r"pwn|heap|stack|rop|format|overflow", re.I), "PWN"),
    (re.compile(r"web|sqli|xss|jwt|http", re.I), "WEB"),
    (re.compile(r"steg", re.I), "STEGO"),
    (re.compile(r"forensic|pcap|memdump", re.I), "FORENSICS"),
    (re.compile(r"osint|recon", re.I), "OSINT"),
    (re.compile(r"chain|solidity|evm", re.I), "BLOCKCHAIN"),
]


def normalize_type(name: str, chal_id: str) -> tuple[str, str]:
    """Return (canonical_type, raw_prefix). raw_prefix is empty if none."""
    raw = ""
    if " - " in name:
        raw = name.split(" - ", 1)[0].strip()
        key = raw.upper()
        if key in TYPE_MAP:
            return TYPE_MAP[key], raw
    # No recognised prefix — try id-based keyword fallback.
    for pattern, t in KEYWORD_FALLBACK:
        if pattern.search(chal_id) or pattern.search(name):
            return t, raw
    return "MISC", raw


def display_name(name: str) -> str:
    """Strip the 'TYPE - 100 -' prefix to leave just the human name."""
    parts = [p.strip() for p in name.split(" - ")]
    if len(parts) == 1:
        return parts[0]
    # Drop leading parts that are type or pure number until we hit a word part.
    while parts and (
        parts[0].upper() in TYPE_MAP
        or parts[0].isdigit()
        or re.fullmatch(r"\d+\s*pts?", parts[0], re.I)
    ):
        parts.pop(0)
    return " - ".join(parts) if parts else name


def main() -> None:
    index: dict[str, dict] = {}
    for module in sorted(ROOT.glob("*/module.yml")):
        ctf_dir = module.parent.name
        try:
            data = yaml.safe_load(module.read_text()) or {}
        except yaml.YAMLError as exc:
            print(f"warn: {module}: {exc}", file=sys.stderr)
            continue
        ctf_id = data.get("id", ctf_dir)
        ctf_name = data.get("name", ctf_dir)
        challenges = []
        for c in data.get("challenges") or []:
            cid = c.get("id")
            if not cid:
                continue
            raw_name = (c.get("name") or cid).strip()
            ctype, raw_prefix = normalize_type(raw_name, cid)
            chal_path = module.parent / cid
            challenges.append(
                {
                    "id": cid,
                    "name": display_name(raw_name),
                    "raw_name": raw_name,
                    "type": ctype,
                    "raw_type": raw_prefix,
                    "path": f"{ctf_dir}/{cid}",
                    "exists": chal_path.is_dir(),
                }
            )
        index[ctf_dir] = {
            "id": ctf_id,
            "name": ctf_name,
            "path": ctf_dir,
            "challenges": challenges,
        }
    OUT.write_text(json.dumps(index, indent=2, sort_keys=False) + "\n")
    total = sum(len(v["challenges"]) for v in index.values())
    print(f"wrote {OUT.relative_to(ROOT)} — {len(index)} CTFs, {total} challenges")


if __name__ == "__main__":
    main()
