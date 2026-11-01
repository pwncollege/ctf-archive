# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Web/portfolio)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/portfolio/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `app.py`
- `static/`
- `templates/`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it maps the flag file to the root-only `/flag` and launches the Flask app.
- The app listens on `0.0.0.0:5000` (it makes internal requests to `127.0.0.1:5000`, so keep that port); reach it at `http://localhost:5000/`.
- Flask and requests are present in the base image; the container has no network, so nothing is installed at runtime.
