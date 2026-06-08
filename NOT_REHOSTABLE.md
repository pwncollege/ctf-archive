
## Rigorous-validation removals & minimal fixes (verified on pwn.college)
- **breakthesyntaxctf2026/cursed** — REMOVED. Live-LLM service; no local artifact yields a flag. Minimal fix: not feasible without the original model/server.
- **thcon2026/break-the-chain** — REMOVED. Crypto oracle; `client.py` connects to a server (port 4242) that is NOT bundled. Minimal fix: bundle the oracle server (reading `/flag`) + a `run.sh`, convert to direct-flag (XOR-malleability bit-flip then reveals `/flag`).
- **kalmarctf2026/monodoom-eternal** — REMOVED. Crypto oracle; repo had only a `live-state.json` scrape, oracle `chal.py` missing; full solve needs the live adaptive-lattice oracle. Minimal fix: ship `chal.py` oracle (reading `/flag`) + `run.sh`, convert to direct-flag.

## Rigorously re-solved but solver incomplete (flag from orcal capture; kept, re-solve pending)
- umassctf2026/lost-and-found, umassctf2026/lego-clicker, umassctf2026/take-a-slice
