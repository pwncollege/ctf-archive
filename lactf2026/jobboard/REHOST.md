# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/lactf2026/jobboard/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/jobboard/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `admin-bot.js`
- `app.js`
- `flag.txt`
- `flagCheck`
- `package-lock.json`
- `package.json`
- `site/application.html`
- `site/applied.html`
- `site/index.html`
- `site/job.html`
- `site/login/index.html`
- `site/style.css`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the Node.js dependencies from the copied bundle before starting the service.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
- Replace bundled local flag files with a symlink or runtime mapping to `/flag` before exposing the challenge.
