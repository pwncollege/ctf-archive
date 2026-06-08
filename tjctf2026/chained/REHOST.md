# REHOSTING

Link to files: [TJCTF 2026](https://ctftime.org/event/3195)

## Challenge Setup

Use the docker-compose.yml to build and run the challenge.

```
docker compose up --build
```

The admin bot is part of the compose: the `app` service serves the web challenge (port 5000) and the `bot` service is the admin bot that visits submitted URLs (`POST /visit`, published on port 8080). The bot shares the app's network namespace so the admin browser reaches the app over loopback.

The flag is read from `/flag` inside the bot container (`admin_bot.read_flag` reads `FLAG_FILE`, default `/flag`). `Dockerfile.bot` writes a self-contained build-time default to `/flag`; replace it on deploy (e.g. mount `./flag:/flag:ro` on the `bot` service).
