# REHOSTING

## Challenge Setup

Copy the challenge files into the deployment directory (a typical pwn.college layout is `/challenge/`),
keeping the file tree identical. The challenge is a Flask app (`index.py`) plus its static content.

Artifacts:
- `.init`
- `index.py`
- `requirements.txt`
- `index.css`, `robots.txt`, `index.svg`
- `assets/`, `templates/`

## Runtime Notes

- The bundled `.init` runs as root at container start: it symlinks `/flag` to `/challenge/flag.txt` and
  starts the Flask app on `0.0.0.0:8000` (run as root so it can read the root-only `/flag`).
- Dependencies: Flask (present in the base image; otherwise `pip3 install -r requirements.txt`).
- No `flagCheck` is used — the flag is read from `/flag` at runtime.
