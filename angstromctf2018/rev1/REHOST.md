# REHOSTING

Files can be found here: [ångstromCTF 2018](https://2018.angstromctf.com/challenges)

## Challenge Setup
This challenge has only one file which which is `rev1_32` which does not have any dependencies. It does however need a `flag` file in the current directory for the challenge to give the flag.

## Flag Linking
This challenge calls a flag file in the current directory so had to link the flag with /flag which is the pwn.college text:
Checks if the file flag exists and if not then it creates a link between flag and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag 2>/dev/null
```