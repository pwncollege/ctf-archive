# REHOSTING

Files can be found here: [Chain of Rope](https://2019.angstromctf.com/challenges)

## Challenge Setup
There are no dependency files for `chain_of_rope` or `chain_of_rope.c`.

## Flag Linking
This challenge calls a flag file in the current directory so had to link the flag with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null

