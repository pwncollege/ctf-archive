#!/bin/bash
set -ex
here="$(realpath "$(dirname "$0")")"
cd "$here"

./site/init.sh
./admin/init.sh
