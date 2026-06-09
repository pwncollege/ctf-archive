# REHOSTING

Link to files: [UMass CTF 2026](https://ctftime.org/event/2937)

## Challenge Setup
Use docker-compose.yml (or the Dockerfile) to build and run the challenge; the flag is read from /flag.

This is a multi-service stack (nginx, cache_proxy, backend, admin, and a Puppeteer admin bot). Bring it up with `docker compose up --build`; the player-facing entrypoint is the `cache_proxy` service on port 5555. A build-time `ARG TEST_FLAG` writes a placeholder flag to `/flag` in the backend image; override it (or mount the real `/flag`) when hosting.
