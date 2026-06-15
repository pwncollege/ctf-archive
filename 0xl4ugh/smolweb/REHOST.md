# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/0xl4ugh/smolweb/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/smolweb/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `init.sh`
- `main.py`
- `requirements.txt`
- `templates/base.html`
- `templates/ratings_page.html`
- `templates/report_page.html`
- `templates/search_page.html`

## Runtime Notes

- After copying the bundle, mark any shipped launchers, scripts, or binaries executable if the original challenge expects direct execution.
- Run the bundled initialization script before the first launch if the challenge depends on generated state or file placement.
- Use the archived Python entrypoint from the copied bundle and keep the working directory aligned with the original file layout.
