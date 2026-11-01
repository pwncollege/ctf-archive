# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Scripting/TimeWarp)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/timewarp/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `TimeWarp.c`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it maps the flag file to the root-only `/flag`, compiles `TimeWarp.c`, and serves the binary over `socat` on `0.0.0.0:19004` as root so the winning player's session can read `/flag`.
- `gcc` and `socat` are present in the base image; the container has no network, so nothing is installed at runtime.
