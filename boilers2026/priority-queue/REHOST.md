# REHOSTING

Link to files: [b01lers CTF 2026](https://ctftime.org/event/3153)

## Challenge Setup
The challenge binary is setuid-root. It opens a **relative** `flag.txt` from its working directory, so the rehost must place a `flag.txt -> /flag` symlink alongside the binary in `/challenge` (`ln -s /flag /challenge/flag.txt`); the setuid binary then reads the real root-only `/flag`. The intended exploit is a heap UAF info-leak (euid-preserving — no shell, so it works under setuid).
