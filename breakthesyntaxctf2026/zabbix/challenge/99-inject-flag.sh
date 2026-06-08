#!/bin/bash
# Runs during MySQL container init (after zabbix.sql has been loaded).
# Reads the flag from /flag and stores its base64 in the "White Hats Gym"
# host description, where the challenge expects it to be exfiltrated via the
# Zabbix host.get API. Defaults to a test flag if /flag is absent.
set -e

FLAG_FILE="/flag"
if [ ! -f "$FLAG_FILE" ]; then
    FLAG_FILE="/flag.txt"
fi
if [ ! -f "$FLAG_FILE" ]; then
    echo "BtSCTF{rehost_test_default}" > /tmp/.flag
    FLAG_FILE="/tmp/.flag"
fi

FLAG_B64="$(base64 -w0 "$FLAG_FILE")"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" <<SQL
UPDATE hosts SET description='${FLAG_B64}' WHERE hostid=10516;
SQL

echo "** injected flag into host 10516 description"
