# REHOSTING

Link to files: [Break The Syntax CTF 2026](https://ctftime.org/event/3137)

## Challenge Setup

This challenge contains all files needed; use docker-compose.yml to build the setup.

The flag is read from `/flag` at runtime. The image ships a default test flag (`BtSCTF{rehost_test_pokecollector}`); to deploy with the real flag, mount it over `/flag` (see the commented volume in `docker-compose.yml`).
