# REHOSTING

Link to files: [KalmarCTF 2026](https://ctftime.org/event/2983)

## Challenge Setup
Use docker-compose.yml (or the Dockerfile) to build and run the challenge; the flag is read from /flag.

This is a multi-service stack: a `web` Go service (port 8081, needs access to the Docker socket), an `extractor` Python service (port 5000) that reads `/flag`, and an `apikey-setup` init container that seeds the shared `/shared/apikey`. Bring it up with `docker compose up --build`. A build-time `ARG TEST_FLAG` writes a placeholder flag to `/flag` in the extractor image; override it (or mount the real `/flag`) when hosting.
