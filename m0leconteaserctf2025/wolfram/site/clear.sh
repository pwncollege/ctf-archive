#!/bin/bash
set -e
here="$(realpath "$(dirname "$0")")"
cd "$here"

sudo rm -rf \
    ./*.log \
    ./*.pem \
    ./venv*
