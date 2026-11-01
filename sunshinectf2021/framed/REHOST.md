# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Misc/framed)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/framed/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.flag.sha256`
- `flagCheck`
- `framed.mkv`

## Runtime Notes

- Serve `framed.mkv` byte-for-byte; do not re-encode or transcode it, as re-encoding would corrupt the challenge data.
- Install the static-flag helper (`flagCheck`) alongside the challenge files and make it setuid-root so it can read the root-only `/flag`: `chmod u+s /challenge/framed/flagCheck`.
- This is a static-artifact challenge, so publish the copied files for download / offline inspection instead of starting a long-running service.
