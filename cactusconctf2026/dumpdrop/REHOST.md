# REHOSTING

## Challenge Setup

The challenge artifacts for this entry are stored outside the git repo at `../ctf-archive-external/cactusconctf2026/dumpdrop/` relative to the `ctf-archive` repo root.
Copy that bundle into the target deployment directory before launching the challenge. A typical pwn.college layout is `/challenge/dumpdrop/`, but keep the copied file tree identical to the archived bundle.

Artifacts in the external bundle:
- `.env.example`
- `package-lock.json`
- `package.json`
- `public/`
- `server.js`

## Runtime Notes

- Run `npm install` from the copied project root before launching the service.
- Start the application from the copied project root with `node server.js`.
- Keep the working directory at the project root so the relative `public/` and `uploads/` paths resolve correctly.
- Provide a non-empty `APPRISE_URL` environment variable so the notification hook remains enabled.
- Expose `/flag.txt` or an equivalent path expected by the archived application.
- Run the service with an account that can read `/flag.txt`.
