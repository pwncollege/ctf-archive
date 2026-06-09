# REHOSTING

Link to files: [CyberGame 2026](https://ctftime.org/event/3142)

## Challenge Setup
Use docker-compose.yaml (or the Dockerfile) to build and run the challenge; the flag is read from /flag.

The service exposes a socat-wrapped PowerShell jail on TCP port 6666. A build-time `ARG TEST_FLAG` writes a placeholder flag to `/flag`; override it (or mount the real `/flag`) when hosting.
