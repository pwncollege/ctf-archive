# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup
Use docker-compose.yml to build the challenge.

## Flag Injection
The flag is read from `/flag.txt` inside the `web` container by the
post-exploitation webshell. A default test flag is baked into the image. To
inject the real flag, build with `--build-arg FLAG='...'` (the `FLAG` build ARG
in the Dockerfile writes `/flag.txt`), or replace `/flag.txt` in the running
`web` container.

## Notes
- Base images are pulled from Docker Hub (`wordpress:6-php8.1-apache`,
  `mariadb:10.6`); only the `web` service is built locally (WordPress core +
  bundled plugins + vendored `firebase/php-jwt`).
- The frontend is exposed on port 8080.
- Intended solve: register a WooCommerce customer at `/my-account/`, pull the
  REST nonce from the localized `MyPlugin.nonce`, call
  `GET /wp-json/legacy/v1/get-token` to obtain a JWT, then
  `POST /wp-json/legacy/v1/change-email` with mass-assigned
  `ID`/`role=administrator` to promote the account (broken access control /
  mass assignment in the custom `my-plugin`). With admin, upload a PHP plugin
  via `plugin-install.php?tab=upload` for code execution and read `/flag.txt`.
