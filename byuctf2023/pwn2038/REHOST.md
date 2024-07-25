# REHOSTING

Files can be found here: [2038](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/BYUCTF/2023/pwn/2038/README.md)

## Challenge Setup
There are no dependecy files for `2038`. It does however need a `flag.txt` file in the current directory for the challenge to give the flag.

## Flag Linking
This challenge calls a flag file in the current directory so had to link the flag with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```