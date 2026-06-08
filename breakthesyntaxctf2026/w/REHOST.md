# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup

Use the docker-compose.yml (or the Dockerfile) to build and run the challenge.

```
docker compose up --build
```

The admin bot is part of the build: a single container runs both the web app (port 8000) and the admin bot (port 3000, internal). When a post is published, `server.js` forwards it to the bot, which visits the post in a headless Chromium, logs in as the admin, and types the flag into the admin Notes textarea.

The flag is read from `/flag` inside the container by the admin bot (`bot.js` reads `FLAG_FILE`, default `/flag`). The Dockerfile writes a self-contained build-time default to `/flag`; replace it on deploy (e.g. mount `./flag:/flag:ro`).
