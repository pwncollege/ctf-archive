FROM mysql:8.0

# Default test flag; replace /flag at deploy time to inject the real flag.
RUN echo "BtSCTF{rehost_test_default}" > /flag && chmod 444 /flag

# zabbix.sql loads first, then 99-inject-flag.sh stores base64(/flag) into the
# "White Hats Gym" host description.
COPY zabbix.sql /docker-entrypoint-initdb.d/00-zabbix.sql
COPY 99-inject-flag.sh /docker-entrypoint-initdb.d/99-inject-flag.sh
