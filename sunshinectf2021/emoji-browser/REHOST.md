# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Web/emoji-browser)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/emoji-browser/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `assets/`
- `index.css`
- `index.py`
- `index.svg`
- `requirements.txt`
- `robots.txt`
- `templates/`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it maps the flag file to the root-only `/flag` and launches the service (run as root so it can read `/flag`).
- The service listens on `0.0.0.0:8000`; reach it at `http://localhost:8000/`.
- Dependencies are present in the base image (the container has no network, so nothing is installed at runtime).
