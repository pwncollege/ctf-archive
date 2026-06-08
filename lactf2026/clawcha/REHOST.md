# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/lactf2026/clawcha/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/clawcha/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `app.js`
- `flag.txt`
- `flagCheck`
- `inventory.js`
- `package-lock.json`
- `package.json`
- `site/clawcha/game.js`
- `site/clawcha/index.html`
- `site/clawcha/style.css`
- `site/img/flag.png`
- `site/index.html`
- `site/main.js`
- `site/style.css`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the Node.js dependencies from the copied bundle before starting the service.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
- Replace bundled local flag files with a symlink or runtime mapping to `/flag` before exposing the challenge.
