# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/amateursctf/normal/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/normal/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `Main.class`
- `flagCheck`
- `run.sh`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Use the bundled launcher script as the startup entrypoint when present.
- Run the copied Java artifact with the runtime expected by the original challenge.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
