#!/bin/sh
sudo --preserve-env --background -- "/opt/bin/entry_point.sh"
sudo --user="appuser" --group="appgroup" --preserve-env --background -- /usr/bin/socat "TCP-LISTEN:8000,reuseaddr,fork" "EXEC:/root/admin/review.sh"
# sudo --user="appuser" --group="appgroup" --preserve-env --background -- /usr/bin/socat "TCP-LISTEN:8000,reuseaddr,fork" "EXEC:/root/admin/review.sh,stderr"
sleep infinity
