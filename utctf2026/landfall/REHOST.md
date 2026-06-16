# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/utctf2026/landfall/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/landfall/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.flag.sha256`
- `Modified_KAPE_Triage_Files.zip`
- `briefing.txt`
- `checkpointA.zip`
- `flagCheck`
- Additional archival support files are present in the external bundle but are not listed here because they are not needed for deployment.

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the bundled static-flag compatibility helper alongside the challenge files if the archived challenge expects the original flag value rather than `/flag` directly.
- This appears to be a static-artifact challenge, so publish the copied bundle for download or offline inspection instead of starting a long-running service.
