#!/bin/sh
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-$(openssl rand -base64 33)}"
echo "ADMIN_USER=$ADMIN_USER"
echo "ADMIN_PASSWORD=$ADMIN_PASSWORD"
export ADMIN_USER
export ADMIN_PASSWORD