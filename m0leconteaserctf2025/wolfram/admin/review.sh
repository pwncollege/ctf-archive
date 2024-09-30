#!/bin/sh
timeout --signal=KILL 60 "/root/admin/review.py"
ps --no-headers -A -o "stat,pid,ppid" | awk '/^[Zz]\s/{ print $3 }' | xargs --no-run-if-empty kill -9