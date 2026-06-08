#!/usr/bin/env bash
set -e

pip install --quiet --force-reinstall "crab-0.1.0-cp314-cp314-manylinux_2_34_x86_64.whl"
python "$(dirname "$0")/crabuoros.py"
