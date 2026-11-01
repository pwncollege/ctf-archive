# REHOSTING

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/audiocrypto/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.flag.sha256`
- `flagCheck`
- `encrypt_wav.py`
- `flag_encrypted.wav`

## Runtime Notes

- Mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the static-flag helper (`flagCheck`) alongside the challenge files and make it setuid-root so it can read the root-only `/flag`: `chmod u+s /challenge/audiocrypto/flagCheck`.
- This is a static-artifact challenge, so publish the copied files for download / offline inspection instead of starting a long-running service.
