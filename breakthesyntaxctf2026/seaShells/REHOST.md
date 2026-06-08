# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup
Use the Dockerfile to build the docker image for the challenge and then run it.
The container exposes the Next.js web app on port 8000 and OpenSSH on port 22.

## Flag Injection
The flag is read from `/flag` at runtime, root-only (`chmod 400`, owner root). A
default test flag is baked into the image (`/flag`); mount or copy the real flag
to `/flag` to override it. `/root/flag.txt` is a symlink to `/flag` (the
privesc chain's read target). Because `/flag` is root-only, the web RCE user
(`romaric`) cannot read it directly — the full privilege-escalation chain is
required.

## Intended Solve
1. Next.js 15.0.0 / React 19.0.0 server-action deserialization RCE
   (CVE-2025-55182, "React2Shell") via a crafted multipart `POST /` with
   `Next-Action`/`text/x-component`, giving code execution as `romaric`.
2. Read `/home/romaric/creds.db`; crack the SHA-256 backup key
   (`f2efc9d9...` -> `shellfish`).
3. SSH in as `abdul` with that password.
4. `abdul` can overwrite the root cron target `/opt/scripts/backup.sh`
   (`* * * * * root /opt/scripts/backup.sh`); overwrite it to read `/flag` as
   root, then wait for the next cron tick.
