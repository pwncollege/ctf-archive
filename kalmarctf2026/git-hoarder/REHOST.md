# REHOSTING

Link to files: [KalmarCTF 2026](https://ctftime.org/event/2983)

## Challenge Setup
Use compose.yml (or the Dockerfile) to build and run the challenge; the flag is read from /flag.

The service exposes a socat-wrapped `clone.py` git-clone handler on TCP port 1444. A build-time `ARG TEST_FLAG` writes a placeholder flag to `/flag`; override it (or mount the real `/flag`) when hosting.
