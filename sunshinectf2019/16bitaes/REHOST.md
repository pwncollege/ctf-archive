# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Crypto/16BitAES)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/16bitaes/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `server.py`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it maps the flag file to the root-only `/flag` and serves `server.py` over `socat` on `0.0.0.0:19003`.
- `server.py` was ported from Python 2 to Python 3 (print/input/hex encoding) for the modern base image; the challenge logic is unchanged.
- `python3` (with `pycryptodome`) and `socat` are present in the base image; the container has no network, so nothing is installed at runtime.
