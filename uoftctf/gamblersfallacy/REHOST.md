# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/uoftctf/gamblersfallacy/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/gamblersfallacy/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `Dockerfile`
- `chall.py`
- `flag`
- `flagCheck`
- `run.sh`
- `serverseed`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Use the bundled launcher script as the startup entrypoint when present.
- If you are using the archived container workflow, build and run the provided `Dockerfile` from the external artifact directory.
- Use the archived Python entrypoint from the copied bundle and keep the working directory aligned with the original file layout.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
- Replace bundled local flag files with a symlink or runtime mapping to `/flag` before exposing the challenge.
