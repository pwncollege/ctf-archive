# REHOSTING

Link to files: [UMass CTF 2026](https://ctftime.org/event/2937)

## Challenge Setup
Use docker-compose.yml (or the Dockerfile) to build and run the challenge; the flag is read from /flag.

This is a multi-service stack: the Spring Boot newspaper app, an `editorial` Node service, and a `developer` report bot (headless Chromium) that holds the flag cookie. Bring it up with `docker compose up --build`. A build-time `ARG TEST_FLAG` writes a placeholder flag to `/flag` in the developer image; override it (or mount the real `/flag`) when hosting.
