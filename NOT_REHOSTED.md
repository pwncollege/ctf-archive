# Complete Challenge Accounting (real CTFtime events)

**This PR rehosts 83 challenges across 15 CTFtime events** (69 with flagCheck, 14 direct-flag). The list below accounts for the *remaining* solved challenges not yet rehosted. (Note: the per-line REHOSTED tags undercount — several rehosted challenges use short slugs that don't string-match their CTFtime display names here.)

Every solved challenge in the local CTF folder's real-event CTFs, classified.

- **REHOSTED**: in this PR with flagCheck + .flag.sha256 (flag confirmed on pwn.college)
- **direct-flag (pwn/web)**: reads /flag at runtime; rehostable as direct but needs a live exploit run to validate (like the 14 direct PR challenges) — not yet done
- **non-rehostable**: OSINT / hardware — don't fit pwn.college's model
- **static (pending)**: static-flag challenge whose flag wasn't cleanly auto-extracted (decoys/placeholders) — needs manual flag recovery

## breakthesyntaxctf2026
- `bugxxor` [web] — direct-flag (web) — needs exploit validation
- `cart_blanche` [web] — direct-flag (web) — needs exploit validation
- `crabuoros` [rev] — REHOSTED ✅
- `cursed` [misc] — REHOSTED ✅
- `far` [web] — direct-flag (web) — needs exploit validation
- `fcp` [rev] — REHOSTED ✅
- `flagchecker` [rev] — REHOSTED ✅
- `minfs` [misc] — REHOSTED ✅
- `my_first_game` [rev] — REHOSTED ✅
- `pokecollector` [web] — direct-flag (web) — needs exploit validation
- `poni_barn` [pwn] — direct-flag (pwn) — needs exploit validation
- `seashells` [web] — direct-flag (web) — needs exploit validation
- `shellcode_1_11` [pwn] — direct-flag (pwn) — needs exploit validation
- `shellcode_2_22` [pwn] — direct-flag (pwn) — needs exploit validation
- `stepping_stones` [rev] — REHOSTED ✅
- `w` [web] — direct-flag (web) — needs exploit validation
- `zabbix` [web] — direct-flag (web) — needs exploit validation

## codegate2026
- `ghost` [crypto] — static (crypto) — flag recovery pending
- `greybox` [rev] — REHOSTED ✅
- `oldschool` [rev] — static (rev) — flag recovery pending
- `welcomeflag` [misc] — static (misc) — flag recovery pending

## cybergame2026
- `[1` [offensive] — static (offensive) — flag recovery pending
- `[12` [crypto] — static (crypto) — flag recovery pending
- `[13` [malware] — static (malware) — flag recovery pending
- `[17` [osint] — non-rehostable (osint)
- `[18` [crypto] — static (crypto) — flag recovery pending
- `[20` [offensive] — static (offensive) — flag recovery pending
- `[22` [crypto] — static (crypto) — flag recovery pending
- `[23` [malware] — static (malware) — flag recovery pending
- `[24` [forensics] — static (forensics) — flag recovery pending
- `[25` [malware] — static (malware) — flag recovery pending
- `[27` [rev] — static (rev) — flag recovery pending
- `[3` [crypto] — static (crypto) — flag recovery pending
- `[30` [forensics] — static (forensics) — flag recovery pending
- `[32` [crypto] — static (crypto) — flag recovery pending
- `[33` [forensics] — static (forensics) — flag recovery pending
- `[35` [forensics] — static (forensics) — flag recovery pending
- `[36` [forensics] — static (forensics) — flag recovery pending
- `[39` [offensive] — static (offensive) — flag recovery pending
- `[4` [osint] — non-rehostable (osint)
- `[44` [osint] — non-rehostable (osint)
- `[46` [malware] — static (malware) — flag recovery pending
- `[47` [osint] — non-rehostable (osint)
- `[48` [osint] — non-rehostable (osint)
- `[6` [forensics] — static (forensics) — flag recovery pending
- `[7` [rev] — static (rev) — flag recovery pending
- `[8` [offensive] — static (offensive) — flag recovery pending

## kalmarctf2026
- `0racle` [rev] — static (rev) — flag recovery pending
- `astralogy` [pwn] — direct-flag (pwn) — needs exploit validation
- `customainer` [web] — direct-flag (web) — needs exploit validation
- `evilbabykalmarctf` [misc] — static (misc) — flag recovery pending
- `flag-checker` [rev] — static (rev) — flag recovery pending
- `git-hoarder` [misc] — static (misc) — flag recovery pending
- `monodoom-eternal` [crypto] — REHOSTED ✅
- `nix-revenge` [rev] — static (rev) — flag recovery pending
- `rbg-plus` [crypto] — static (crypto) — flag recovery pending
- `rbg-plus-plus` [crypto] — static (crypto) — flag recovery pending
- `reluess-your-inihbitions` [misc] — static (misc) — flag recovery pending
- `rootbabykalmarctf` [web] — direct-flag (web) — needs exploit validation
- `ternarya` [crypto] — static (crypto) — flag recovery pending

## thcon2026
- `break-the-chain` [crypto] — REHOSTED ✅
- `gunnar-s-vacation-bis-picture-1` [osint] — non-rehostable (osint)
- `incredibly-protected-notifications` [web] — direct-flag (web) — needs exploit validation
- `m4term4xima-s-hint-part-1-2` [rev] — REHOSTED ✅
- `panic-in-the-northern-quadrant-part-1-3` [web] — direct-flag (web) — needs exploit validation
- `silent-signer` [rev] — REHOSTED ✅
- `thcity-authentication-collapse-part-1-2` [web] — direct-flag (web) — needs exploit validation

## tjctf2025
- `bit-leak` [crypto] — REHOSTED ✅
- `chained` [web] — direct-flag (web) — needs exploit validation
- `check-the-fine-print` [forensics] — REHOSTED ✅
- `crashout` [crypto] — REHOSTED ✅
- `delta-doodle` [misc] — REHOSTED ✅
- `greetings` [pwn] — direct-flag (pwn) — needs exploit validation
- `mind-blowers` [misc] — REHOSTED ✅
- `minervas-stopwatch` [crypto] — REHOSTED ✅
- `multiplication-as-a-service` [crypto] — REHOSTED ✅
- `obscure-crusher-1` [forensics] — REHOSTED ✅
- `ox78` [pwn] — direct-flag (pwn) — needs exploit validation
- `polaroid` [rev] — REHOSTED ✅
- `remoose` [rev] — REHOSTED ✅
- `treasure-hunt` [web] — direct-flag (web) — needs exploit validation
- `voice-in-the-packet` [forensics] — REHOSTED ✅
- `wavy` [crypto] — REHOSTED ✅

## umassctf2026
- `batcave-bitflips` [rev] — static (rev) — flag recovery pending
- `brick-by-brick` [web] — direct-flag (web) — needs exploit validation
- `brick-city-office-space` [pwn] — direct-flag (pwn) — needs exploit validation
- `browser-boss-fight` [web] — direct-flag (web) — needs exploit validation
- `click-here-for-free-bricks` [forensics] — static (forensics) — flag recovery pending
- `deep-down` [misc] — REHOSTED ✅
- `funny-business` [osint] — non-rehostable (osint)
- `lego-clicker` [rev] — REHOSTED ✅
- `lost-and-found` [forensics] — REHOSTED ✅
- `ninja-nerds` [forensics] — REHOSTED ✅
- `smart-brick-v2` [hardware] — non-rehostable (hardware)
- `son-of-a-sith` [osint] — non-rehostable (osint)
- `take-a-slice` [misc] — REHOSTED ✅
- `the-accursed-lego-bin` [crypto] — REHOSTED ✅
- `the-block-city-times` [web] — direct-flag (web) — needs exploit validation
- `we-have-at-home` [osint] — non-rehostable (osint)

## vishwactf2026
- `Fragmented Evidence` [forensics] — REHOSTED ✅
- `Heap of Secrets` [web] — direct-flag (web) — needs exploit validation
- `Keymaster Secrets` [web] — direct-flag (web) — needs exploit validation
- `Kratos` [rev] — REHOSTED ✅
- `PROCEDURAL LABYRINTH` [crypto] — REHOSTED ✅
- `The-Lost-Client` [osint] — non-rehostable (osint)
- `UnderWorld - P1` [misc] — static (misc) — flag recovery pending
- `Webhook Pinger` [web] — direct-flag (web) — needs exploit validation
- `ghost_in_the_pipeline` [web] — direct-flag (web) — needs exploit validation

## Final sweep — remaining real-CTF challenges accounting (rigorous pass)
### In rigorous validation now (direct-flag, being exploit-validated)
- pwn (8): tjctf2025 greetings/ox78, breakthesyntax poni_barn/shellcode_1_11/shellcode_2_22, midnightsun blkmgk/chain/drop
- web w/ app source (3): tjctf2025 chained, breakthesyntax w + far

### Not rehostable — missing app artifact (no source/handout in the orcal capture)
- tjctf2025/treasure-hunt (only cookies.txt)
- breakthesyntax: pokecollector, cart_blanche, captcha, bugxxor, seashells, zabbix (zabbix also needs a CVE-vulnerable Zabbix server)
- midnightsun: cmashine, smachine, slopgamez (flag known but no local binary/app)

### Not rehostable — dynamic/per-instance flags
- midnightsun speed-2..5 (hex per-instance flags)

### Untouched real CTFtime events with ZERO clean rehostable yield
- boilers2026 — all flags are placeholders (bctf{real_flag_will_be_here}); nothing solved
- defconquals2026 — no captured solves (mostly unsolved attempts); also active/sensitive
- umdctf2026 — 0 flags captured

### Skipped — NOT CTFtime events (practice/exercise platforms)
- cryptohack, 247ctf, hackarena (article/public-exercises/gynvael collection), livectf-replay-viewer (a tool), sniper_tests (test dir), defconquals2026-untouched (backup copy)

## GitHub source search for missing-artifact challenges
- **BtS 2026 web (6)** — full source published at `PWrWhiteHats/BtS-2026-Writeups` (retrieved): captcha + seaShells (single-container → feasible to rehost); pokecollector + bugxxor (app+DB multi-container → heavier); zabbix (23-service Zabbix stack) + cart-blanche (multi-container + large DB dump) → impractical on pwn.college.
- **TJCTF 2026 treasure-hunt** — no source published (official 2026 repo not up; community repos are writeups only).
- **Midnight Sun cmashine/smachine/slopgamez** — writeups only (Ridful repo), no source published.

## Attribution fix
- The `tjscctf` folder is **TJCTF 2026** (per its PROGRESS.md + community repos Draxen-0x/TJCTF-2026, Vatsallavari/TJCTF-2026), not 2025. Renamed to `tjctf2026` (CTFtime event 3195, team 53812, 15-17 May 2026).
