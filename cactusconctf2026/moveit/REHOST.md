# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/cactusconctf2026/moveit/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/moveit/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `crypto-moveit.py`
- `flagCheck`

## Runtime Notes

- Launch the challenge with `sage -python crypto-moveit.py`.
- Set `HOME` or the Sage cache directories to a writable path before launch if the deployment environment restricts writes to the default home directory.
- Keep `.flag.sha256` in the same directory as `flagCheck`.
- Install `flagCheck` alongside the challenge files when you want the archived static flag to convert to the pwn.college flag.
