# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup
Use the Dockerfile to build the docker image for the challenge and then run it.
The container exposes the Next.js web app on port 8000 and OpenSSH on port 22.

## Flag Setup
The flag is read from `/flag` at runtime, root-only (`chmod 400`, owner root). A
default test flag is baked into the image (`/flag`); mount or copy the real flag
to `/flag` to override it. `/root/flag.txt` is a symlink to `/flag`.
