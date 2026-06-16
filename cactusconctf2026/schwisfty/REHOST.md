# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/cactusconctf2026/schwisfty/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/schwisfty/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `chall.py`
- `flagCheck`

## Runtime Notes

- Launch the challenge with `python3 chall.py`.
- The service is interactive and prints its menu on startup, so keep stdin attached when exposing it as a local process or socket-backed service.
- Keep `.flag.sha256` in the same directory as `flagCheck`.
- Install `flagCheck` alongside the challenge files when you want the archived static flag to convert to the pwn.college flag.
