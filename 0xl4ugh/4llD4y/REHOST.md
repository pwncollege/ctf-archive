# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/0xl4ugh/4llD4y/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/4llD4y/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `app.js`
- `flatnest-1.0.1.tgz`
- `happy-dom-20.3.1.tgz`
- `init.sh`
- `package-lock.json`
- `package.json`
- `supervisord.conf`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Run the bundled initialization script before the first launch if the challenge depends on generated state or file placement.
- Install the Node.js dependencies from the copied bundle before starting the service.
