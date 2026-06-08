# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/2841)

## Challenge Setup
Use docker-compose.yml to build the challenge.

## Flag Injection
The flag is read from `/flag` inside the `zabbix-db` container at init time
(`challenge/99-inject-flag.sh`), base64-encoded, and stored in the
"White Hats Gym" host (hostid 10516) description, where the intended exploit
exfiltrates it via the Zabbix `host.get` JSON-RPC API. A default test flag is
baked into the image (`/flag`); mount or copy the real flag to `/flag` in the
`zabbix-db` container to override it.

## Notes
- Official Zabbix 6.0.0 images are pulled from Docker Hub
  (`zabbix/zabbix-server-mysql:ubuntu-6.0.0`,
  `zabbix/zabbix-web-nginx-mysql:ubuntu-6.0.0`); only `zabbix-db` is built locally.
- The frontend is exposed on port 8080. The intended solve is:
  crack the SHA-256 hash hint (`-> xibbazzz`), log in as user `bts` over
  `api_jsonrpc.php`, then call `host.get` and base64-decode the host description.
