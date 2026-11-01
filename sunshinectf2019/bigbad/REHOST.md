# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Forensics/BigBad)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/bigbad/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.flag.sha256`
- `flagCheck`
- `BigBad.png`

## Runtime Notes

- Install the static-flag helper (`flagCheck`) alongside the challenge files and make it setuid-root so it can read the root-only `/flag`: `chmod u+s /challenge/bigbad/flagCheck`.
- This is a static-artifact challenge, so publish the copied files for download / offline inspection instead of starting a long-running service.
