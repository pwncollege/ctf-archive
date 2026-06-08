# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup
Use the Dockerfile to build the docker image for the challenge and then run it.

## Flag Injection
The Flask app reads the flag from `/flag` at runtime (`app/secret.py`). A default
test flag is baked into the image (`/flag`); mount or copy the real flag to
`/flag` to override it. The flag is served only after the client solves four
consecutive IntegralCaptcha challenges (HS256-signed token, `count` 0 -> 3).
