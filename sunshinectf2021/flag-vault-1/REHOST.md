# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Web/flag-vault-1)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/flag-vault-1/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `flag-vault-1` (prebuilt static linux/amd64 binary)
- `favicon.png`
- `views/`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it reads the root-only `/flag` into the `FLAG` environment variable and launches the service from `/challenge`.
- The service listens on `0.0.0.0:8000`; reach it at `http://localhost:8000/`.
- `flag-vault-1` is a statically linked Go binary with no runtime dependencies; keep it executable. It renders its HTML templates from `./views` and serves itself for download at `/download`, so keep the binary, `favicon.png`, and `views/` together in the same directory.
