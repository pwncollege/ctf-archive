# CTF Archive — Challenge Search & Dojo Builder

An in-dojo page that lets educators find challenges by **topic** and **difficulty**,
choose how many they want, and download a ready-to-use **`dojo.yml`** that imports those
challenges from `ctf-archive`. It is static and algorithmic — no backend, no LLM at query time.

Served at **`/ctf-archive/search`** (wired via `pages:` in the repo-root `dojo.yml`; the page
is the repo-root `search.md`).

## How it works

Type a topic (`cryptography`, `heap`, `xss`, `format-string`, …) — matching is concept-aware
(tag sub-tokens, stem/prefix, and a synonym map, so `cryptography` ≡ `crypto`, `pcap` ≡
`packet-capture`, etc.). Use **commas for several topics** (`heap,crypto,web`). Press Enter to
open the top-N as a selectable list, tick the ones you want, then download the `dojo.yml`.

Difficulty (`beginner`→`insane`) is computed from two signals: pwn.college solve counts and
per-event CTF points, blended, with a per-category fallback.

## Files

| File | Role |
|------|------|
| `../search.md` | The generated dojo page (styles + data + logic inlined). **Served at `/ctf-archive/search`.** |
| `tags.json` | Per-challenge technique tags `{tags, prerequisites, summary}`, keyed by `event/challenge`. |
| `build_search_index.py` | Builds `index.json` from `../challenges.json`, `tags.json`, and `../solves.csv`. |
| `index.json` | The built search index (one record per challenge + facets). Generated. |
| `embed/` | Widget source: `embed_style.css`, `embed_body.html`, `embed_app.js`, and `build_embed.py` which inlines them + `index.json` into `../search.md`. |

## Regenerate

```bash
python3 search/build_search_index.py   # -> search/index.json
python3 search/embed/build_embed.py     # -> search.md
```
