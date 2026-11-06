#!/usr/bin/env python3
"""Assemble the in-dojo search page `search.md` (at the repo root) by inlining the
namespaced widget: styles + markup + data + logic into one self-contained file.

The dojo serves this page at /ctf-archive/search (see `pages:` in dojo.yml). Because
ctf-archive is an official dojo, its page markdown is rendered as raw HTML, so the
inlined <style>/<script> run in the browser.

Run from the repo root after rebuilding the index:
    python3 search/build_search_index.py
    python3 search/embed/build_embed.py
"""
import json
from pathlib import Path

HERE = Path(__file__).resolve().parent      # search/embed
SEARCH = HERE.parent                         # search
REPO = SEARCH.parent                         # repo root

style = (HERE / "embed_style.css").read_text()
body = (HERE / "embed_body.html").read_text()
app = (HERE / "embed_app.js").read_text()
index = json.loads((SEARCH / "index.json").read_text())

# Inline data as a JS global (minified), then the widget logic.
data = "window.CTF_INDEX = " + json.dumps(index, ensure_ascii=False, separators=(",", ":")) + ";"
script_inner = data + "\n" + app

# Escape every "</" as "<\/" so a tag/summary containing "</script>" (or a template's
# </div>) cannot close the inlined <script> early. Harmless in JS: "\/" === "/".
script_inner = script_inner.replace("</", "<\\/")

search_md = f"<style>\n{style}\n</style>\n\n{body}\n<script>\n{script_inner}\n</script>\n"
(REPO / "search.md").write_text(search_md)
print(f"wrote {REPO / 'search.md'} ({len(search_md) // 1024} KB)")
