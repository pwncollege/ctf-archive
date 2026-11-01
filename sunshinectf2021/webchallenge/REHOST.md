# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Reversing/WebChallenge)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/webchallenge/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.flag.sha256`
- `flagCheck`
- `Molot.otf`
- `better_secure.png`
- `database.bat`
- `decrypt.encrypted.ps1`
- `encrypt.ps1`
- `fontawesome-all.min.css`
- `html.hta`
- `pceye_compliant.png`
- `root.css`

## Runtime Notes

- Mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Install the static-flag helper (`flagCheck`) alongside the challenge files and make it setuid-root so it can read the root-only `/flag`: `chmod u+s /challenge/webchallenge/flagCheck`.
- This is a static-artifact challenge, so publish the copied files for download / offline inspection instead of starting a long-running service.
