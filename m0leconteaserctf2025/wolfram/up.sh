#!/bin/bash
set -ex
here="$(realpath "$(dirname "$0")")"
cd "$here"

"./init.sh"
. "env.sh"
docker compose up --build --pull always "$@" || docker compose down
