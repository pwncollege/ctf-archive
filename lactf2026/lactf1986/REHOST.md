# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/lactf2026/lactf1986/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/lactf1986/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `CHALL.EXE`
- `CHALL.IMG`
- `flagCheck`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
