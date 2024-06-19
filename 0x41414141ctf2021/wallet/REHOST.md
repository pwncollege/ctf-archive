# REHOSTING

Link to files: [0x41414141 CTF](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/0x41414141/2021/crypto/Delegate-wallet/wallet.py)

## Challenge Setup
This challenge just has a file called `wallet.py` which do not have any dependencies.

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.