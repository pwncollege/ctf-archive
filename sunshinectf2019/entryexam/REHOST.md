# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Scripting/EntryExam)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/entryexam/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `app.py`
- `static/`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it maps the flag file to the root-only `/flag` and launches the Flask app.
- The app listens on `0.0.0.0:5000`; reach it at `http://localhost:5000/`.
- Flask and Pillow are present in the base image; the container has no network, so nothing is installed at runtime.
- `app.py`'s session-timestamp comparison was adjusted for modern Flask's timezone-aware session serialization (a deployment-compatibility fix; challenge behavior is unchanged).
