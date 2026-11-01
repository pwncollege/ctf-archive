# REHOSTING

Files can be found here: [SunshineCTF 2019](https://github.com/SunshineCTF/SunshineCTF-2019-Public/tree/master/Web/WrestlerNameGenerator)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/wrestlernamegenerator/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `generate.php`
- `index.html`
- `wrestlerwave.jpg`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts: it reads the root-only `/flag` into the `FLAG` environment variable (the app serves it to requests that originate from the server itself) and serves the PHP app.
- It runs the built-in PHP server on `0.0.0.0:80` with `PHP_CLI_SERVER_WORKERS=5` so the app can service a request made back to `127.0.0.1` while handling the original request.
- `php` and `socat` are present in the base image; the container has no network, so nothing is installed at runtime.
- `generate.php` reads the flag via `getenv("FLAG")` (was `$_ENV["FLAG"]`) so it works under the built-in PHP server regardless of `variables_order`; challenge behavior is unchanged.
