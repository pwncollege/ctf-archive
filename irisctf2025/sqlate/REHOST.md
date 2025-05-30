# REHOSTING

Files can be found here: [IrisCTF 2025](https://github.com/IrisSec/IrisCTF-2025-Challenges/blob/main/sqlate/)

## Challenge Setup
The main `vuln` file has been made using various c files which are provided by the organizers.

## Libc Error
If the system is running a different `Ubuntu` version than the one in which `vuln` was compiled it might give you an error. To deal with this situation and to make the challenge compatible with `pwn.college`, a few changes were made:
1. First of all the challenge has been re-compiled using the makefile.
2. The `main.c` file uses `cat /flag` instead of what the original file had to open up `pwn.college` flag.