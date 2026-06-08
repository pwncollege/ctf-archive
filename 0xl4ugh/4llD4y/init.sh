#!/bin/sh

echo "$FLAG" >> /flag_$(head -c 8 /dev/urandom | od -An -tx1 | tr -d ' ').txt
unset FLAG
export FLAG=Nope
supervisord -c /etc/supervisor/conf.d/supervisord.conf