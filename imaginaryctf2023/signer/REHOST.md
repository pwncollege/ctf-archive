# REHOSTING

Files can be found here: [Signer](https://github.com/ImaginaryCTF/ImaginaryCTF-2023-Challenges/tree/main/Crypto/signer)

## Challenge Setup
There are no dependency files for `main.py`.

## Flag Linking
This challenge calls a flag file in the current directory so had to link the flag with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```