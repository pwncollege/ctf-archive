# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup

Use the Dockerfile to build and run the challenge.

```
docker build -t far .
docker run -p 80:80 far
```

The flag is read from `/flag` (the application's LFI target `/flag.txt` is a symlink to `/flag`). The Dockerfile writes a self-contained build-time default to `/flag`; replace it on deploy.
